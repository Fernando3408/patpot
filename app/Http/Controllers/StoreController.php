<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Store;
use App\Services\AuditService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $query = Store::with('customer');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $stores = $query->orderBy('name')->get();

        return view('stores.index', compact('stores'));
    }

    public function create()
    {
        $customers = Customer::where('status', true)->get();

        return view('stores.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'code' => 'required|string|max:100',
            'name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ]);

        $exists = Store::where('customer_id', $validated['customer_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'code' => 'Ese código de sala ya existe para este cliente.',
                ])
                ->withInput();
        }

        $store = Store::create($validated);
        AuditService::log('CREACIÓN DE SALA', "Creó sala: {$store->name}", $store);

        return redirect('/salas');
    }

    public function edit(Store $store)
    {
        $customers = Customer::where('status', true)->get();

        return view('stores.edit', compact('store', 'customers'));
    }

    public function show(Store $store)
    {
        $store->load('customer');
        return view('stores._detail', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        try {
            if ($request->ajax()) {
                $rules = [
                    'customer_id' => 'sometimes|required|exists:customers,id',
                    'code' => 'sometimes|required|string|max:100',
                    'name' => 'sometimes|nullable|string|max:255',
                    'city' => 'sometimes|nullable|string|max:255',
                    'region' => 'sometimes|nullable|string|max:255',
                    'status' => 'sometimes|required|boolean',
                ];
            } else {
                $rules = [
                    'customer_id' => 'required|exists:customers,id',
                    'code' => 'required|string|max:100',
                    'name' => 'nullable|string|max:255',
                    'city' => 'nullable|string|max:255',
                    'region' => 'nullable|string|max:255',
                    'status' => 'required|boolean',
                ];
            }
            $validated = $request->validate($rules);

            $customerId = $validated['customer_id'] ?? $store->customer_id;
            $exists = Store::where('customer_id', $customerId)
                ->where('code', $validated['code'] ?? $store->code)
                ->where('id', '!=', $store->id)
                ->exists();

            if ($exists) {
                if ($request->ajax()) {
                    return response()->json(['errors' => ['code' => ['Ese código de sala ya existe para este cliente.']]], 422);
                }
                return back()
                    ->withErrors([
                        'code' => 'Ese código de sala ya existe para este cliente.',
                    ])
                    ->withInput();
            }

            $store->update($validated);
            AuditService::log('ACTUALIZACIÓN DE SALA', "Actualizó sala: {$store->name}", $store);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect('/salas');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(Store $store)
    {
        $store->update(['deleted_by' => auth()->id()]);
        $store->delete();
        AuditService::log('ELIMINACIÓN DE SALA', "Eliminó sala: {$store->name}", $store);

        return redirect('/salas');
    }
}
