<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
use App\Models\Store;
use App\Services\AuditService;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request): View
    {
        $query = Order::with(['customer', 'store', 'lines.product', 'shipments']);

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

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['lines' => array_values(array_filter($request->input('lines', []), fn (array $line): bool => filled($line['product_id'] ?? null)))]);
        $data = $request->validate(['number' => ['required', 'string', 'max:255', 'unique:orders,number'], 'customer_id' => ['required', 'exists:customers,id'], 'store_id' => ['nullable', 'exists:stores,id'], 'ordered_on' => ['required', 'date'], 'delivery_on' => ['nullable', 'date'], 'notes' => ['nullable', 'string'], 'lines' => ['required', 'array', 'min:1'], 'lines.*.product_id' => ['required', 'exists:products,id'], 'lines.*.boxes' => ['required', 'integer', 'gt:0'], 'lines.*.price_box' => ['nullable', 'numeric', 'min:0'], 'lines.*.discount_pct' => ['nullable', 'numeric', 'between:0,100']]);
        $this->ensureStoreBelongsToCustomer($data);
        foreach ($data['lines'] as &$line) {
            if (blank($line['price_box'] ?? null)) {
                $price = Price::query()->where('customer_id', $data['customer_id'])->where('product_id', $line['product_id'])->first();
                $line['price_box'] = $price?->effective_price ?? Product::query()->findOrFail($line['product_id'])->sale_price_box;
            }
        } unset($line);
        $order = Order::create(collect($data)->except('lines')->all() + ['status' => 'pending']);
        $order->lines()->createMany($data['lines']);
        AuditService::log('CREACIÓN DE PEDIDO', "Creó pedido: {$order->number}", $order);

        return redirect('/pedidos');
    }

    public function show(Order $order): View
    {
        $order->load('customer', 'store', 'lines.product');
        return view('orders._detail', compact('order'));
    }

    public function dispatch(Request $request, Order $pedido): RedirectResponse
    {
        $data = $request->validate(['quantities' => ['required', 'array'], 'quantities.*' => ['nullable', 'integer', 'min:0'], 'shipped_on' => ['required', 'date']]);
        $this->inventoryService->dispatchOrder($pedido, $data['quantities'], $data['shipped_on']);

        return redirect('/pedidos')->with('success', 'Despacho registrado correctamente.');
    }

    public function edit(Order $pedido): View
    {
        return view('orders.edit', ['order' => $pedido, 'customers' => Customer::where('status', true)->orderBy('business_name')->get(), 'stores' => Store::where('status', true)->orderBy('name')->get()]);
    }

    public function update(Request $request, Order $pedido)
    {
        try {
            if ($request->ajax()) {
                $rules = [
                    'number' => ['sometimes', 'required', 'string', 'max:255', 'unique:orders,number,'.$pedido->id],
                    'customer_id' => ['sometimes', 'required', 'exists:customers,id'],
                    'store_id' => ['sometimes', 'nullable', 'exists:stores,id'],
                    'ordered_on' => ['sometimes', 'required', 'date'],
                    'delivery_on' => ['sometimes', 'nullable', 'date'],
                    'notes' => ['sometimes', 'nullable', 'string'],
                ];
            } else {
                $rules = [
                    'number' => ['required', 'string', 'max:255', 'unique:orders,number,'.$pedido->id],
                    'customer_id' => ['required', 'exists:customers,id'],
                    'store_id' => ['nullable', 'exists:stores,id'],
                    'ordered_on' => ['required', 'date'],
                    'delivery_on' => ['nullable', 'date'],
                    'notes' => ['nullable', 'string'],
                ];
            }
            $data = $request->validate($rules);
            $this->ensureStoreBelongsToCustomer($data);
            $pedido->update($data);
            AuditService::log('ACTUALIZACIÓN DE PEDIDO', "Actualizó pedido: {$pedido->number}", $pedido);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect('/pedidos')->with('success', 'Pedido actualizado correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
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
     * @param  array{customer_id: int|string, store_id?: int|string|null}  $data
     */
    private function ensureStoreBelongsToCustomer(array $data): void
    {
        if (blank($data['store_id'] ?? null)) {
            return;
        }

        $belongsToCustomer = Store::query()
            ->whereKey($data['store_id'])
            ->where('customer_id', $data['customer_id'])
            ->exists();

        if (! $belongsToCustomer) {
            throw ValidationException::withMessages([
                'store_id' => 'La sala seleccionada no pertenece al cliente del pedido.',
            ]);
        }
    }
}
