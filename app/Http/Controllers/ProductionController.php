<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Production;
use App\Services\AuditService;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request): View
    {
        $query = Production::with('product');

        if ($request->filled('from')) {
            $query->where('planned_on', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('planned_on', '<=', $request->to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $productions = $query->latest('planned_on')->get();

        return view('productions.index', compact('productions'));
    }

    public function create(): View
    {
        return view('productions.create', ['products' => Product::where('status', 'active')->with('recipes.input')->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['number' => ['required', 'string', 'max:255', 'unique:productions,number'], 'product_id' => ['required', 'exists:products,id'], 'planned_boxes' => ['required', 'integer', 'gt:0'], 'planned_on' => ['required', 'date'], 'notes' => ['nullable', 'string']]);
        $production = Production::create($data);
        AuditService::log('CREACIÓN DE PRODUCCIÓN', "Creó producción: {$production->number}", $production);

        return redirect('/produccion');
    }

    public function show(Production $produccion): View
    {
        $produccion->load('product');
        return view('productions._detail', ['production' => $produccion]);
    }

    public function close(Request $request, Production $produccion): RedirectResponse
    {
        $data = $request->validate(['actual_boxes' => ['required', 'integer', 'gt:0'], 'completed_on' => ['required', 'date']]);
        $this->inventoryService->closeProduction($produccion, (float) $data['actual_boxes'], $data['completed_on']);

        return redirect('/produccion')->with('success', 'Producción cerrada correctamente.');
    }

    public function edit(Production $produccion): View
    {
        return view('productions.edit', ['production' => $produccion, 'products' => Product::where('status', 'active')->with('recipes.input')->orderBy('name')->get()]);
    }

    public function update(Request $request, Production $produccion)
    {
        try {
            if ($produccion->status === 'closed') {
                if ($request->ajax()) {
                    return response()->json(['errors' => ['production' => ['No puedes editar una producción cerrada.']]], 422);
                }
                return back()->withErrors(['production' => 'No puedes editar una producción cerrada.']);
            }

            if ($request->ajax()) {
                $rules = [
                    'number' => ['sometimes', 'required', 'string', 'max:255', 'unique:productions,number,'.$produccion->id],
                    'product_id' => ['sometimes', 'required', 'exists:products,id'],
                    'planned_boxes' => ['sometimes', 'required', 'integer', 'gt:0'],
                    'planned_on' => ['sometimes', 'required', 'date'],
                    'status' => ['sometimes', 'required', 'in:planned,in_progress,closed'],
                    'notes' => ['sometimes', 'nullable', 'string'],
                ];
            } else {
                $rules = [
                    'number' => ['required', 'string', 'max:255', 'unique:productions,number,'.$produccion->id],
                    'product_id' => ['required', 'exists:products,id'],
                    'planned_boxes' => ['required', 'integer', 'gt:0'],
                    'planned_on' => ['required', 'date'],
                    'status' => ['sometimes', 'required', 'in:planned,in_progress,closed'],
                    'notes' => ['nullable', 'string'],
                ];
            }
            $data = $request->validate($rules);
            $produccion->update($data);
            AuditService::log('ACTUALIZACIÓN DE PRODUCCIÓN', "Actualizó producción: {$produccion->number}", $produccion);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect('/produccion')->with('success', 'Producción actualizada correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(Production $produccion): RedirectResponse
    {
        if ($produccion->status === 'closed') {
            return back()->withErrors(['delete' => 'No puedes eliminar una producción que ya fue cerrada.']);
        }

        $produccion->delete();
        AuditService::log('ELIMINACIÓN DE PRODUCCIÓN', "Eliminó producción: {$produccion->number}", $produccion);

        return redirect('/produccion')->with('success', 'Producción eliminada correctamente.');
    }
}
