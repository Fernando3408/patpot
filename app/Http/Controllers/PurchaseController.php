<?php

namespace App\Http\Controllers;

use App\Models\Input;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\AuditService;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request): View
    {
        $query = Purchase::with(['supplier', 'lines.input']);

        if ($request->filled('from')) {
            $query->where('ordered_on', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('ordered_on', '<=', $request->to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $purchases = $query->latest('ordered_on')->get();

        return view('purchases.index', compact('purchases'));
    }

    public function create(): View
    {
        return view('purchases.create', ['suppliers' => Supplier::where('status', true)->orderBy('name')->get(), 'inputs' => Input::where('status', true)->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['lines' => array_values(array_filter($request->input('lines', []), fn (array $line): bool => filled($line['input_id'] ?? null)))]);
        $data = $request->validate(
            ['number' => ['required', 'string', 'max:255', 'unique:purchases,number'], 'supplier_id' => ['required', 'exists:suppliers,id'], 'ordered_on' => ['required', 'date'], 'expected_on' => ['nullable', 'date'], 'notes' => ['nullable', 'string'], 'lines' => ['required', 'array', 'min:1'], 'lines.*.input_id' => ['required', 'distinct', 'exists:inputs,id'], 'lines.*.ordered_quantity' => ['required', 'numeric', 'gt:0'], 'lines.*.unit_cost' => ['required', 'numeric', 'min:0']],
            ['number.unique' => 'El número de compra ya existe.']
        );
        $this->ensureWholeUnitLines($data['lines']);
        $purchase = Purchase::create(collect($data)->except('lines')->all() + ['status' => 'pending']);
        foreach ($data['lines'] as $line) {
            $purchase->lines()->create($line);
            Input::query()->findOrFail($line['input_id'])->increment('transit', $line['ordered_quantity']);
        }
        AuditService::log('CREACIÓN DE COMPRA', "Creó compra: {$purchase->number}", $purchase);

        return redirect('/compras');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'lines.input');
        return view('purchases._detail', compact('purchase'));
    }

    public function receive(Request $request, Purchase $compra): RedirectResponse
    {
        $data = $request->validate(['received' => ['required', 'array'], 'received.*' => ['nullable', 'numeric', 'min:0']]);
        $compra->load('lines.input');
        foreach ($compra->lines as $line) {
            if ($this->requiresWholeQuantity($line->input) && fmod((float) ($data['received'][$line->id] ?? 0), 1.0) !== 0.0) {
                throw ValidationException::withMessages(['received' => "{$line->input->name} debe recibirse en unidades enteras."]);
            }
        }
        $this->inventoryService->receivePurchase($compra, $data['received']);

        return redirect('/compras')->with('success', 'Recepción registrada correctamente.');
    }

    public function edit(Purchase $compra): View
    {
        $compra->load('lines.input');
        $inputs = Input::where('status', true)->orderBy('name')->get();
        $canEditLines = $compra->lines->every(fn ($line) => (float) $line->received_quantity === 0);

        return view('purchases.edit', [
            'purchase' => $compra,
            'suppliers' => Supplier::where('status', true)->orderBy('name')->get(),
            'inputs' => $inputs,
            'canEditLines' => $canEditLines,
        ]);
    }

    public function update(Request $request, Purchase $compra)
    {
        try {
            if ($request->ajax()) {
                $rules = [
                    'number' => ['sometimes', 'required', 'string', 'max:255', 'unique:purchases,number,'.$compra->id],
                    'supplier_id' => ['sometimes', 'required', 'exists:suppliers,id'],
                    'ordered_on' => ['sometimes', 'required', 'date'],
                    'expected_on' => ['sometimes', 'nullable', 'date'],
                    'notes' => ['sometimes', 'nullable', 'string'],
                    'ordered_quantity' => ['sometimes', 'required', 'integer', 'min:1'],
                ];
            } else {
                $rules = [
                    'number' => ['required', 'string', 'max:255', 'unique:purchases,number,'.$compra->id],
                    'supplier_id' => ['required', 'exists:suppliers,id'],
                    'ordered_on' => ['required', 'date'],
                    'expected_on' => ['nullable', 'date'],
                    'notes' => ['nullable', 'string'],
                    'lines' => ['nullable', 'array'],
                    'lines.*.ordered_quantity' => ['required_with:lines', 'integer', 'min:1'],
                ];
            }
            $data = $request->validate($rules, ['number.unique' => 'El número de compra ya existe.']);
            $compra->update($data);

            if ($request->ajax() && isset($data['ordered_quantity']) && $compra->lines->count() === 1) {
                $compra->lines()->first()->update(['ordered_quantity' => $data['ordered_quantity']]);
            }

            if ($request->has('lines') && $compra->lines->every(fn ($line) => (float) $line->received_quantity === 0)) {
                $lines = $request->input('lines', []);
                foreach ($lines as $lineId => $lineData) {
                    $compra->lines()->where('id', $lineId)->update([
                        'ordered_quantity' => $lineData['ordered_quantity'],
                    ]);
                }
            }

            AuditService::log('ACTUALIZACIÓN DE COMPRA', "Actualizó compra: {$compra->number}", $compra);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect('/compras')->with('success', 'Compra actualizada correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(Purchase $compra): RedirectResponse
    {
        if ((float) $compra->lines->sum('received_quantity') > 0) {
            return back()->withErrors(['delete' => 'No puedes eliminar una compra que ya tiene recepciones registradas.']);
        }

        foreach ($compra->lines as $line) {
            Input::query()->findOrFail($line->input_id)->decrement('transit', $line->ordered_quantity);
        }

        $compra->lines()->delete();
        $compra->delete();
        AuditService::log('ELIMINACIÓN DE COMPRA', "Eliminó compra: {$compra->number}", $compra);

        return redirect('/compras')->with('success', 'Compra eliminada correctamente.');
    }

    private function ensureWholeUnitLines(array $lines): void
    {
        $inputs = Input::query()->whereIn('id', collect($lines)->pluck('input_id'))->get()->keyBy('id');
        foreach ($lines as $line) {
            $input = $inputs[$line['input_id']];
            if ($this->requiresWholeQuantity($input) && fmod((float) $line['ordered_quantity'], 1.0) !== 0.0) {
                throw ValidationException::withMessages(['lines' => "{$input->name} debe ingresarse en unidades enteras."]);
            }
        }
    }

    private function requiresWholeQuantity(Input $input): bool
    {
        $unit = mb_strtolower($input->unit);

        return str_contains($unit, 'kg') || str_contains($unit, 'caja');
    }
}
