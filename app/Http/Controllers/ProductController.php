<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('recipes.input');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->get();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => ['required', 'string', 'max:255', Rule::unique(Product::class)],
            'grams' => 'required|integer|min:0',
            'units_per_box' => 'required|integer|min:1',
            'stock_boxes' => 'required|integer|min:0',
            'min_stock_boxes' => 'required|integer|min:0',
            'sale_price_box' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $product = Product::create($validated);
        AuditService::log('CREACIÓN DE PRODUCTO', "Creó producto: {$validated['name']}", $product);

        return redirect('/productos');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function show(Product $product)
    {
        $product->load('recipes.input');
        return view('products._detail', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        try {
            if ($request->ajax()) {
            $rules = [
                'name' => 'sometimes|required|string|max:255',
                'sku' => ['sometimes', 'required', 'string', 'max:255', Rule::unique(Product::class)->ignore($product)],
                'stock_boxes' => 'sometimes|integer|min:0',
                'min_stock_boxes' => 'sometimes|integer|min:0',
                'sale_price_box' => 'sometimes|nullable|numeric|min:0',
                'status' => 'sometimes|required|in:active,inactive',
            ];
            } else {
                $rules = [
                    'name' => 'required|string|max:255',
                    'sku' => ['required', 'string', 'max:255', Rule::unique(Product::class)->ignore($product)],
                    'grams' => 'required|integer|min:0',
                    'units_per_box' => 'required|integer|min:1',
                    'stock_boxes' => 'required|integer|min:0',
                    'min_stock_boxes' => 'required|integer|min:0',
                    'sale_price_box' => 'required|numeric|min:0',
                    'status' => 'required|in:active,inactive',
                ];
            }
            $validated = $request->validate($rules);

            $product->update($validated);
            $product->load('recipes.input');
            AuditService::log('ACTUALIZACIÓN DE PRODUCTO', "Actualizó producto: {$product->name}", $product);

            if ($request->ajax()) {
                $margin = $product->sale_price_box - $product->cost_per_box;
                return response()->json([
                    'success' => true,
                    'sale_price_box' => $product->sale_price_box,
                    'cost_per_box' => $product->cost_per_box,
                    'margin' => $margin,
                    'margin_pct' => $product->sale_price_box > 0 ? round($margin / $product->sale_price_box * 100, 1) : 0,
                    'production_capacity' => $product->production_capacity,
                ]);
            }
            return redirect('/productos');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(Product $product)
    {
        if ($product->orderLines()->exists()) {
            return back()->withErrors([
                'delete' => 'No puedes eliminar un producto que tiene pedidos asociados.',
            ]);
        }

        if ($product->productions()->exists()) {
            return back()->withErrors([
                'delete' => 'No puedes eliminar un producto que tiene producciones.',
            ]);
        }

        if ($product->recipes()->exists()) {
            return back()->withErrors([
                'delete' => 'No puedes eliminar un producto que tiene recetas definidas.',
            ]);
        }

        if ($product->prices()->exists()) {
            return back()->withErrors([
                'delete' => 'No puedes eliminar un producto que tiene precios de cliente asociados.',
            ]);
        }

        if ($product->retail()->exists()) {
            return back()->withErrors([
                'delete' => 'No puedes eliminar un producto que tiene registros retail asociados.',
            ]);
        }

        $product->update(['deleted_by' => auth()->id()]);
        $product->delete();
        AuditService::log('ELIMINACIÓN DE PRODUCTO', "Eliminó producto: {$product->name}", $product);

        return redirect('/productos')->with('success', 'Producto eliminado correctamente.');
    }
}
