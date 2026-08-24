<?php

namespace App\Http\Controllers;

use App\Models\Input;
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
        $input->load('supplier', 'recipes.product');
        return view('inputs._detail', compact('input'));
    }

    public function update(Request $request, Input $input)
    {
        try {
            if ($request->ajax()) {
                $rules = [
                    'name' => 'sometimes|required|string|max:255',
                    'code' => ['sometimes', 'required', 'string', 'max:255'],
                    'stock' => 'sometimes|required|numeric|min:0',
                    'safety_stock' => 'sometimes|required|numeric|min:0',
                    'unit' => 'sometimes|required|string|max:50',
                    'status' => 'sometimes|required|boolean',
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
                return response()->json(['success' => true]);
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

        $input->update(['deleted_by' => auth()->id()]);
        $input->delete();
        AuditService::log('ELIMINACIÓN DE INSUMO', "Eliminó insumo: {$input->name}", $input);

        return redirect('/insumos');
    }
}
