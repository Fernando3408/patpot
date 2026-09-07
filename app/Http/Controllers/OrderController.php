<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
use App\Models\Store;
use App\Services\AuditService;
use App\Services\InventoryService;
use App\Traits\ValidatesWithLineFormatting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    use ValidatesWithLineFormatting;

    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request): View
    {
        $query = Order::with(['customer', 'store', 'lines.product', 'shipments.lines']);

        if ($request->filled('from')) {
            $query->where('ordered_on', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('ordered_on', '<=', $request->to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest('ordered_on')->get();

        return view('orders.index', compact('orders'));
    }

    public function create(): View
    {
        return view('orders.create', ['customers' => Customer::where('status', true)->orderBy('business_name')->get(), 'stores' => Store::where('status', true)->orderBy('name')->get(), 'products' => Product::where('status', 'active')->orderBy('name')->get(), 'prices' => Price::query()->get()->keyBy(fn (Price $price): string => "{$price->customer_id}-{$price->product_id}")]);
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->merge(['lines' => array_values(array_filter($request->input('lines', []), fn (array $line): bool => filled($line['product_id'] ?? null)))]);
        $data = $this->validateLines($request->all(), ['number' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('orders', 'number')->where(fn ($q) => $q->whereNull('deleted_at'))], 'customer_id' => ['required', 'exists:customers,id'], 'store_id' => ['nullable', 'exists:stores,id'], 'ordered_on' => ['required', 'date'], 'delivery_on' => ['nullable', 'date'], 'notes' => ['nullable', 'string'], 'lines' => ['required', 'array', 'min:1'], 'lines.*.product_id' => ['required', 'distinct', 'exists:products,id'], 'lines.*.boxes' => ['required', 'integer', 'gt:0'], 'lines.*.price_box' => ['nullable', 'numeric', 'min:0']]);
        $this->ensureStoreBelongsToCustomer($data);
        foreach ($data['lines'] as &$line) {
            if (blank($line['price_box'] ?? null)) {
                $line['price_box'] = $this->effectivePriceForCustomerProduct((int) $data['customer_id'], (int) $line['product_id']);
            }
        } unset($line);
        $order = Order::create(collect($data)->except('lines')->all() + ['status' => 'pending']);
        $order->lines()->createMany($data['lines']);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $storedName = uniqid('att_', true).'.'.$file->getClientOriginalExtension();
                $path = $file->storeAs('attachments', $storedName, 'local');
                $order->attachments()->create([
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name' => $storedName,
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'user_id' => auth()->id(),
                ]);
            }
        }

        AuditService::log('CREACIÓN DE PEDIDO', "Creó pedido: {$order->number}", $order);

        if ($request->ajax()) {
            return response()->json(['ok' => true]);
        }

        return redirect('/pedidos');
    }

    public function show(string $pedido): View
    {
        $order = Order::findOrFail($pedido);
        $order->load('customer', 'store', 'lines.product', 'shipments.lines.orderLine.product', 'attachments');

        return view('orders._detail', compact('order'));
    }

    public function dispatch(Request $request, Order $pedido)
    {
        $data = $request->validate([
            'quantities' => ['required', 'array'],
            'quantities.*' => ['nullable', 'integer', 'min:0'],
            'shipped_on' => ['required', 'date'],
            'freight_cost' => ['nullable', 'numeric', 'min:0'],
            'management_cost' => ['nullable', 'numeric', 'min:0'],
            'other_cost' => ['nullable', 'numeric', 'min:0'],
        ]);
        $this->inventoryService->dispatchOrder($pedido, $data['quantities'], $data['shipped_on'], $data);

        if ($request->ajax()) {
            $pedido->refresh()->load('lines', 'shipments.lines.orderLine.product');
            $totalBoxes = $pedido->lines->sum(fn ($l) => (float) $l->boxes);
            $totalDispatched = $pedido->lines->sum(fn ($l) => (float) $l->dispatched_boxes);
            $pct = $totalBoxes > 0 ? round(($totalDispatched / $totalBoxes) * 100) : 0;
            $statusLabel = match ($pedido->status) {
                'completed' => 'Completado',
                'partial' => 'Parcial',
                default => 'Pendiente',
            };
            $history = [];
            $runningTotal = 0;
            foreach ($pedido->shipments->sortBy(fn ($s) => $s->shipped_on ? $s->shipped_on->format('Y-m-d') : '') as $shipment) {
                foreach ($shipment->lines as $sl) {
                    $runningTotal += (float) $sl->boxes;
                    $subtotal = (float) $sl->boxes * (float) ($sl->price_box ?? 0);
                    $costBox = (float) $sl->cost_box + (float) $sl->variable_cost_box;
                    $variableCostTotal = (float) $shipment->freight_cost + (float) $shipment->management_cost + (float) $shipment->other_cost;
                    $history[] = [
                        'shipment_id' => $shipment->id,
                        'date' => $shipment->shipped_on ? $shipment->shipped_on->format('d/m/Y') : '—',
                        'product' => $sl->orderLine->product?->name ?? '—',
                        'boxes' => (int) $sl->boxes,
                        'price_box' => '$'.number_format((float) ($sl->price_box ?? 0), 0, ',', '.'),
                        'subtotal' => '$'.number_format($subtotal, 0, ',', '.'),
                        'cost_box' => '$'.number_format($costBox, 0, ',', '.'),
                        'freight_cost' => '$'.number_format((float) $shipment->freight_cost, 0, ',', '.'),
                        'management_cost' => '$'.number_format((float) $shipment->management_cost, 0, ',', '.'),
                        'other_cost' => '$'.number_format((float) $shipment->other_cost, 0, ',', '.'),
                        'variable_total' => '$'.number_format($variableCostTotal, 0, ',', '.'),
                        'accumulated' => (int) $runningTotal,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'status' => $pedido->status,
                'statusLabel' => $statusLabel,
                'pct' => $pct,
                'totalDispatched' => $totalDispatched,
                'totalBoxes' => $totalBoxes,
                'history' => $history,
                'historyCount' => $pedido->shipments->count(),
            ]);
        }

        return redirect('/pedidos')->with('success', 'Despacho registrado correctamente.');
    }

    public function edit(Order $pedido): View
    {
        $pedido->load('lines.product', 'attachments');
        $this->ensureOrderHasNoDispatches($pedido);

        return view('orders.edit', ['order' => $pedido, 'customers' => Customer::where('status', true)->orderBy('business_name')->get(), 'stores' => Store::where('status', true)->orderBy('name')->get(), 'products' => Product::where('status', 'active')->orderBy('name')->get()]);
    }

    public function update(Request $request, Order $pedido)
    {
        try {
            $pedido->loadMissing('lines');
            $this->ensureOrderHasNoDispatches($pedido);

            if ($request->ajax()) {
                $rules = [
                    'number' => ['sometimes', 'required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('orders', 'number')->ignore($pedido->id)->where(fn ($q) => $q->whereNull('deleted_at'))],
                    'customer_id' => ['sometimes', 'required', 'exists:customers,id'],
                    'store_id' => ['sometimes', 'nullable', 'exists:stores,id'],
                    'ordered_on' => ['sometimes', 'required', 'date'],
                    'delivery_on' => ['sometimes', 'nullable', 'date'],
                    'notes' => ['sometimes', 'nullable', 'string'],
                ];
            } else {
                $rules = [
                    'number' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('orders', 'number')->ignore($pedido->id)->where(fn ($q) => $q->whereNull('deleted_at'))],
                    'customer_id' => ['required', 'exists:customers,id'],
                    'store_id' => ['nullable', 'exists:stores,id'],
                    'ordered_on' => ['required', 'date'],
                    'delivery_on' => ['nullable', 'date'],
                    'notes' => ['nullable', 'string'],
                ];
            }
            $request->merge(['lines' => array_values(array_filter($request->input('lines', []), fn (array $line): bool => filled($line['product_id'] ?? null) && blank($line['remove'] ?? null)))]);
            if (! $request->ajax()) {
                $rules = array_merge($rules, [
                    'lines' => ['required', 'array', 'min:1'],
                    'lines.*.id' => ['nullable', 'integer'],
                    'lines.*.product_id' => ['required', 'distinct', 'exists:products,id'],
                    'lines.*.boxes' => ['required', 'integer', 'gt:0'],
                    'lines.*.price_box' => ['nullable', 'numeric', 'min:0'],
                ]);
            }

            $data = $this->validateLines($request->all(), $rules);
            $this->ensureStoreBelongsToCustomer($data, $pedido);
            foreach ($data['lines'] ?? [] as &$line) {
                if (blank($line['price_box'] ?? null)) {
                    $line['price_box'] = $this->effectivePriceForCustomerProduct((int) $data['customer_id'], (int) $line['product_id']);
                }
            } unset($line);

            DB::transaction(function () use ($pedido, $data): void {
                $pedido->update(collect($data)->except('lines')->all());
                if (isset($data['lines'])) {
                    $this->inventoryService->updateOrderLines($pedido, $data['lines']);
                }
                AuditService::log('ACTUALIZACIÓN DE PEDIDO', "Actualizó pedido: {$pedido->number}", $pedido);
            }, attempts: 5);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }

            return redirect('/pedidos')->with('success', 'Pedido actualizado correctamente.');
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(Order $pedido): RedirectResponse
    {
        if ($pedido->shipments()->exists()) {
            return back()->withErrors(['delete' => 'No puedes eliminar un pedido que ya tiene despachos.']);
        }

        $pedido->lines()->delete();
        $pedido->delete();
        AuditService::log('ELIMINACIÓN DE PEDIDO', "Eliminó pedido: {$pedido->number}", $pedido);

        return redirect('/pedidos')->with('success', 'Pedido eliminado correctamente.');
    }

    /**
     * @param  array{customer_id?: int|string, store_id?: int|string|null}  $data
     */
    private function ensureStoreBelongsToCustomer(array $data, ?Order $order = null): void
    {
        if (blank($data['store_id'] ?? null)) {
            return;
        }

        $customerId = $data['customer_id'] ?? $order?->customer_id;

        $belongsToCustomer = Store::query()
            ->whereKey($data['store_id'])
            ->where('customer_id', $customerId)
            ->exists();

        if (! $belongsToCustomer) {
            throw ValidationException::withMessages([
                'store_id' => 'La sala seleccionada no pertenece al cliente del pedido.',
            ]);
        }
    }

    private function effectivePriceForCustomerProduct(int $customerId, int $productId): float
    {
        $price = Price::query()
            ->where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->first();

        return (float) ($price?->effective_price ?? Product::query()->findOrFail($productId)->sale_price_box);
    }

    private function ensureOrderHasNoDispatches(Order $order): void
    {
        if ($order->lines->contains(fn ($line): bool => (int) $line->dispatched_boxes > 0)) {
            abort(403, 'No puedes editar un pedido que ya tiene productos despachados.');
        }
    }
}
