<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('rut', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')->get();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rut' => 'nullable|string|max:20',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'lead_time_days' => 'required|integer|min:0',
            'payment_terms' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ]);

        $supplier = Supplier::create($validated);
        AuditService::log('CREACIÓN DE PROVEEDOR', "Creó proveedor: {$supplier->name}", $supplier);

        return redirect('/proveedores');
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load('inputs');

        return view('suppliers._detail', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): JsonResponse|RedirectResponse
    {
        try {
            if ($request->ajax()) {
                $rules = [
                    'name' => 'sometimes|required|string|max:255',
                    'rut' => 'sometimes|nullable|string|max:20',
                    'contact_name' => 'sometimes|nullable|string|max:255',
                    'email' => 'sometimes|nullable|email|max:255',
                    'phone' => 'sometimes|nullable|string|max:50',
                    'lead_time_days' => 'sometimes|required|integer|min:0',
                    'payment_terms' => 'sometimes|nullable|string|max:255',
                    'status' => 'sometimes|required|boolean',
                ];
            } else {
                $rules = [
                    'name' => 'required|string|max:255',
                    'rut' => 'nullable|string|max:20',
                    'contact_name' => 'nullable|string|max:255',
                    'email' => 'nullable|email|max:255',
                    'phone' => 'nullable|string|max:50',
                    'lead_time_days' => 'required|integer|min:0',
                    'payment_terms' => 'nullable|string|max:255',
                    'status' => 'required|boolean',
                ];
            }
            $validated = $request->validate($rules);

            $supplier->update($validated);
            AuditService::log('ACTUALIZACIÓN DE PROVEEDOR', "Actualizó proveedor: {$supplier->name}", $supplier);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }

            return redirect('/proveedores');
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Supplier $supplier): JsonResponse|RedirectResponse
    {
        // Protección: si tiene insumos asociados, no lo dejamos borrar de golpe
        if ($supplier->inputs()->exists() || $supplier->purchases()->exists()) {
            return back()->withErrors([
                'delete' => 'No puedes eliminar este proveedor porque tiene insumos o compras asociados.',
            ]);
        }

        $supplier->update(['deleted_by' => auth()->id()]);
        $supplier->delete();
        AuditService::log('ELIMINACIÓN DE PROVEEDOR', "Eliminó proveedor: {$supplier->name}", $supplier);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect('/proveedores');
    }
}
