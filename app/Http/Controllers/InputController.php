<?php

namespace App\Http\Controllers;

use App\Models\Input;
use App\Models\InventoryMovement;
use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\Request;

class InputController extends Controller
{
    public function index(Request $request)
    {
        $query = Input::with(['supplier', 'recipes.product.productions']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $inputs = $query->orderBy('name')->get();

        return view('inputs.index', compact('inputs'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', true)->get();

        return view('inputs.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:inputs,code',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',

            'stock' => 'required|numeric|min:0',
            'safety_stock' => 'required|numeric|min:0',

            'weekly_consumption' => 'required|numeric|min:0',
            'lead_time_days' => 'required|integer|min:0',
            'target_weeks' => 'required|numeric|min:0',

            'min_purchase' => 'required|numeric|min:0',
            'purchase_multiple' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',

            'transit' => 'required|numeric|min:0',

            'supplier_id' => 'nullable|exists:suppliers,id',

            'status' => 'required|boolean',
        ]);

        $input = Input::create($validated);
        AuditService::log('CREACIÓN DE INSUMO', "Creó insumo: {$input->name}", $input);

        return redirect('/insumos');
    }

    public function edit(Input $input)
    {
        $suppliers = Supplier::where('status', true)->get();

        return view('inputs.edit', compact('input', 'suppliers'));
    }

    public function show(Input $input)
    {
        $input->load('supplier', 'recipes.product.productions');
        return view('inputs._detail', compact('input'));
    }

    public function update(Request $request, Input $input)
    {
        try {
            if ($request->ajax()) {
                $rules = [
                    'name' => 'sometimes|required|string|max:255',
                    'code' => ['sometimes', 'required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('inputs', 'code')->ignore($input->id)],
                    'stock' => 'sometimes|required|numeric|min:0',
                    'safety_stock' => 'sometimes|required|numeric|min:0',
                    'weekly_consumption' => 'sometimes|numeric|min:0',
                    'unit' => 'sometimes|required|string|max:50',
                    'status' => 'sometimes|required|boolean',
                    'lead_time_days' => 'sometimes|required|integer|min:0',
                    'target_weeks' => 'sometimes|required|integer|min:0',
                    'min_purchase' => 'sometimes|required|numeric|min:0',
                    'purchase_multiple' => 'sometimes|required|integer|min:1',
                    'unit_cost' => 'sometimes|nullable|numeric|min:0',
                    'transit' => 'sometimes|required|numeric|min:0',
                    'supplier_id' => 'sometimes|nullable|exists:suppliers,id',
                    'category' => 'sometimes|nullable|string|max:255',
                ];
            } else {
                $rules = [
                    'code' => 'required|string|max:255|unique:inputs,code,'.$input->id,
                    'name' => 'required|string|max:255',
                    'category' => 'nullable|string|max:255',
                    'unit' => 'required|string|max:50',
                    'stock' => 'required|numeric|min:0',
                    'safety_stock' => 'required|numeric|min:0',
                    'weekly_consumption' => 'required|numeric|min:0',
                    'lead_time_days' => 'required|integer|min:0',
                    'target_weeks' => 'required|numeric|min:0',
                    'min_purchase' => 'required|numeric|min:0',
                    'purchase_multiple' => 'required|numeric|min:0.01',
                    'unit_cost' => 'required|numeric|min:0',
                    'transit' => 'required|numeric|min:0',
                    'supplier_id' => 'nullable|exists:suppliers,id',
                    'status' => 'required|boolean',
                ];
            }
            $validated = $request->validate($rules);

            $input->update($validated);
            AuditService::log('ACTUALIZACIÓN DE INSUMO', "Actualizó insumo: {$input->name}", $input);

            if ($request->ajax()) {
                $input->refresh();
                $input->load('recipes.product.productions');
                return response()->json([
                    'success' => true,
                    'coverage_days' => $input->coverage_days,
                    'reorder_point' => $input->reorder_point,
                    'projected_stock' => $input->projected_stock,
                    'inventory_level' => $input->inventory_level,
                    'weekly_consumption' => $input->weekly_consumption,
                    'unit_cost' => $input->unit_cost,
                ]);
            }
            return redirect('/insumos');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(Input $input)
    {
        if ($input->recipes()->exists()) {
            return back()->withErrors([
                'delete' => 'No puedes eliminar este insumo porque está usado en una o más recetas.',
            ]);
        }

        if ($input->purchaseLines()->exists()) {
            return back()->withErrors([
                'delete' => 'No puedes eliminar este insumo porque tiene compras asociadas.',
            ]);
        }

        if ($input->inventoryMovements()->exists()) {
            return back()->withErrors([
                'delete' => 'No puedes eliminar este insumo porque tiene movimientos de inventario registrados.',
            ]);
        }

        $input->update(['deleted_by' => auth()->id()]);
        $input->delete();
        AuditService::log('ELIMINACIÓN DE INSUMO', "Eliminó insumo: {$input->name}", $input);

        return redirect('/insumos');
    }

    public function adjust(Request $request, Input $input)
    {
        $validated = $request->validate([
            'type' => 'required|in:add,subtract,set',
            'qty' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        $before = (float) $input->stock;
        $qty = (float) $validated['qty'];

        match ($validated['type']) {
            'add' => $input->stock = $before + $qty,
            'subtract' => $input->stock = max(0, $before - $qty),
            'set' => $input->stock = $qty,
        };

        $input->save();

        InventoryMovement::create([
            'input_id' => $input->id,
            'kind' => 'Ajuste de inventario',
            'quantity' => $input->stock - $before,
            'reference' => strtoupper($validated['type']),
            'notes' => $validated['reason'],
            'user_id' => auth()->id(),
        ]);

        AuditService::log('AJUSTAR INSUMO', "{$input->name}: {$before} → {$input->stock}. {$validated['reason']}", $input);

        return response()->json(['success' => true, 'stock' => $input->stock]);
    }

    public function export(Request $request, string $entity)
    {
        $filename = "PatPot_{$entity}_" . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($entity, $filename) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            match ($entity) {
                'inputs' => $this->exportInputs($handle),
                'products' => $this->exportProducts($handle),
                'retail' => $this->exportRetail($handle),
                default => null,
            };

            fclose($handle);
        };

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ];

        return response()->stream($callback, 200, $headers);
    }

    private function exportInputs($handle): void
    {
        fputcsv($handle, ['Código', 'Nombre', 'Categoría', 'Unidad', 'Stock', 'Seguridad', 'Consumo semanal', 'Plazo (días)', 'Proveedor'], ';');
        foreach (Input::with('supplier')->orderBy('name')->get() as $input) {
            fputcsv($handle, [
                $input->code, $input->name, $input->category, $input->unit,
                $input->stock, $input->safety_stock, $input->weekly_consumption,
                $input->lead_time_days, $input->supplier?->name ?? '',
            ], ';');
        }
    }

    private function exportProducts($handle): void
    {
        fputcsv($handle, ['SKU', 'Nombre', 'Stock (cajas)', 'Precio/caja', 'Costo/caja', 'Estado'], ';');
        foreach (\App\Models\Product::orderBy('name')->get() as $p) {
            fputcsv($handle, [
                $p->sku, $p->name, $p->stock_boxes, $p->sale_price_box, $p->cost_per_box, $p->status,
            ], ';');
        }
    }

    private function exportRetail($handle): void
    {
        fputcsv($handle, ['Sala', 'Código', 'Ciudad', 'Producto', 'SKU', 'Stock', 'Tránsito', 'Venta semanal', 'Quiebre', 'Reposición'], ';');
        foreach (\App\Models\Retail::with('store.customer', 'product')->orderBy('store_id')->get() as $r) {
            fputcsv($handle, [
                $r->store?->name ?? '', $r->store?->code ?? '', $r->store?->city ?? '',
                $r->product?->name ?? '', $r->product?->sku ?? '',
                $r->stock_units, $r->transit_units, $r->weekly_sales,
                $r->is_break ? 'Sí' : 'No', $r->suggested_replenishment_boxes,
            ], ';');
        }
    }
}
