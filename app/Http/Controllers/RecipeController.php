<?php

namespace App\Http\Controllers;

use App\Models\Input;
use App\Models\Product;
use App\Models\Recipe;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $query = Recipe::with(['product.recipes.input', 'input'])->orderBy('product_id');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->orWhereHas('input', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }
        
        $recipes = $query->get();

        return view('recipes.index', compact('recipes'));
    }

    public function create()
    {
        $products = Product::where('status', 'active')->get();
        $inputs = Input::where('status', true)->get();

        return view('recipes.create', compact('products', 'inputs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'input_id' => 'required|exists:inputs,id',
            'qty_per_box' => 'required|numeric|min:0.0001',
        ]);

        $existing = Recipe::withTrashed()
            ->where('product_id', $validated['product_id'])
            ->where('input_id', $validated['input_id'])
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
                $existing->update(['qty_per_box' => $validated['qty_per_box']]);
                AuditService::log('RESTAURACIÓN DE RECETA', "Restauró y actualizó receta de producto ID: {$validated['product_id']}", $existing);
                return redirect('/recetas');
            }
            return back()
                ->withErrors([
                    'input_id' => 'Este insumo ya está registrado en la receta de este producto.',
                ])
                ->withInput();
        }

        $recipe = Recipe::create([
            'product_id' => $validated['product_id'],
            'input_id' => $validated['input_id'],
            'qty_per_box' => $validated['qty_per_box'],
        ]);
        AuditService::log('CREACIÓN DE RECETA', "Creó receta para producto ID: {$validated['product_id']}", $recipe);

        return redirect('/recetas');
    }

    public function edit(Product $product)
    {
        $product->load('recipes');

        $inputs = Input::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('recipes.edit', compact('product', 'inputs'));
    }

    public function show(Product $product)
    {
        $product->load('recipes.input');
        return view('recipes._detail', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'ingredients' => ['required', 'array'],
            'ingredients.*.input_id' => ['required', 'integer', 'exists:inputs,id', 'distinct:strict'],
            'ingredients.*.selected' => ['nullable', 'boolean'],
            'ingredients.*.qty_per_box' => ['nullable', 'numeric', 'min:0.0001'],
        ]);

        $validator->after(function ($validator) use ($request): void {
            $selectedIngredients = collect($request->input('ingredients', []))
                ->filter(fn (mixed $ingredient): bool => is_array($ingredient) && (bool) ($ingredient['selected'] ?? false));

            if ($selectedIngredients->isEmpty()) {
                $validator->errors()->add('ingredients', 'Selecciona al menos un insumo para la receta.');
            }

            $selectedIngredients
                ->filter(fn (array $ingredient): bool => blank($ingredient['qty_per_box'] ?? null))
                ->each(function (array $ingredient, int|string $key) use ($validator): void {
                    $validator->errors()->add("ingredients.{$key}.qty_per_box", 'Ingresa la cantidad por caja para el insumo seleccionado.');
                });
        });

        $validated = $validator->validate();

        $ingredients = collect($validated['ingredients'])
            ->filter(fn (array $ingredient): bool => (bool) ($ingredient['selected'] ?? false))
            ->map(fn (array $ingredient): array => [
                'input_id' => $ingredient['input_id'],
                'qty_per_box' => $ingredient['qty_per_box'],
            ]);

        DB::transaction(function () use ($product, $ingredients): void {
            $product->recipes()
                ->withTrashed()
                ->whereNotIn('input_id', $ingredients->pluck('input_id'))
                ->delete();

            $ingredients->each(function (array $ingredient) use ($product): void {
                $recipe = Recipe::withTrashed()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'input_id' => $ingredient['input_id'],
                    ],
                    ['qty_per_box' => $ingredient['qty_per_box']],
                );

                if ($recipe->trashed()) {
                    $recipe->restore();
                }
            });
        });
        AuditService::log('ACTUALIZACIÓN DE RECETA', "Actualizó receta de producto: {$product->name}", $product);

        return redirect('/recetas');
    }

    public function destroy(Recipe $recipe)
    {
        $recipe->delete();
        AuditService::log('ELIMINACIÓN DE RECETA', 'Eliminó insumo de receta', $recipe);

        return redirect('/recetas');
    }
}
