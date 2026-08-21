<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Retail;
use App\Models\Store;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RetailController extends Controller
{
    public function index(Request $request)
    {
        $query = Retail::with(['store.customer', 'product']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('store', fn($q) => $q->where('code', 'like', "%{$search}%")
                ->orWhereHas('customer', fn($q2) => $q2->where('business_name', 'like', "%{$search}%")))
                ->orWhereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $records = $query->get();

        // Filtro de quiebres: se aplica en PHP porque is_break es un accesor calculado
        if ($request->query('quiebre') == '1') {
            $records = $records->filter(fn ($r) => $r->is_break)->values();
        }

        // Paginación manual para colecciones
        $page = $request->get('page', 1);
        $perPage = 20;
        $paginator = new LengthAwarePaginator(
            $records->forPage($page, $perPage),
            $records->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('retail.index', ['records' => $paginator]);
    }

    public function create()
    {
        $stores = Store::where('status', true)->with('customer')->get();
        $products = Product::where('status', 'active')->get();

        return view('retail.create', compact('stores', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'cataloged' => 'required|boolean',
            'stock_units' => 'required|numeric|min:0',
            'transit_units' => 'required|numeric|min:0',
            'weekly_sales' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'reorder_point' => 'required|numeric|min:0',
        ]);

        $exists = Retail::where('store_id', $validated['store_id'])
            ->where('product_id', $validated['product_id'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'product_id' => 'Ya existe un registro retail para esta sala y este producto.',
                ])
                ->withInput();
        }

        $retail = Retail::create($validated);
        AuditService::log('CREACIÓN DE RETAIL', 'Creó registro retail', $retail);

        return redirect('/retail');
    }

    public function edit(Retail $retail)
    {
        $stores = Store::where('status', true)->with('customer')->get();
        $products = Product::where('status', 'active')->get();

        return view('retail.edit', compact('retail', 'stores', 'products'));
    }

    public function show(Retail $retail)
    {
        $retail->load('store.customer', 'product');
        return view('retail._detail', compact('retail'));
    }

    public function update(Request $request, Retail $retail)
    {
        try {
            if ($request->ajax()) {
                $rules = [
                    'store_id' => 'sometimes|required|exists:stores,id',
                    'product_id' => 'sometimes|required|exists:products,id',
                    'cataloged' => 'sometimes|required|boolean',
                    'stock_units' => 'sometimes|required|numeric|min:0',
                    'transit_units' => 'sometimes|required|numeric|min:0',
                    'weekly_sales' => 'sometimes|required|numeric|min:0',
                    'min_stock' => 'sometimes|required|numeric|min:0',
                    'reorder_point' => 'sometimes|required|numeric|min:0',
                ];
            } else {
                $rules = [
                    'store_id' => 'required|exists:stores,id',
                    'product_id' => 'required|exists:products,id',
                    'cataloged' => 'required|boolean',
                    'stock_units' => 'required|numeric|min:0',
                    'transit_units' => 'required|numeric|min:0',
                    'weekly_sales' => 'required|numeric|min:0',
                    'min_stock' => 'required|numeric|min:0',
                    'reorder_point' => 'required|numeric|min:0',
                ];
            }
            $validated = $request->validate($rules);

            $storeId = $validated['store_id'] ?? $retail->store_id;
            $productId = $validated['product_id'] ?? $retail->product_id;

            $exists = Retail::where('store_id', $storeId)
                ->where('product_id', $productId)
                ->where('id', '!=', $retail->id)
                ->exists();

            if ($exists) {
                if ($request->ajax()) {
                    return response()->json(['errors' => ['product_id' => ['Ya existe un registro retail para esta sala y este producto.']]], 422);
                }
                return back()
                    ->withErrors([
                        'product_id' => 'Ya existe un registro retail para esta sala y este producto.',
                    ])
                    ->withInput();
            }

            $retail->update($validated);
            AuditService::log('ACTUALIZACIÓN DE RETAIL', 'Actualizó registro retail', $retail);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect('/retail');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(Retail $retail)
    {
        $retail->update(['deleted_by' => auth()->id()]);
        $retail->delete();
        AuditService::log('ELIMINACIÓN DE RETAIL', 'Eliminó registro retail', $retail);

        return redirect('/retail');
    }
}
