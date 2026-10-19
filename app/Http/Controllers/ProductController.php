<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with('recipes.input');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->get();

        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        return view('products.create', ['referenceImages' => $this->referenceImages()]);
    }

    private function referenceImages(): array
    {
        $directory = base_path('docs/Productos img');
        return is_dir($directory) ? array_values(array_filter(scandir($directory), fn ($file) => in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']))) : [];
    }

    public function referenceImage(string $filename)
    {
        abort_if(str_contains($filename, '..'), 404);
        $path = str_starts_with($filename, 'product-reference/')
            ? storage_path('app/public/' . $filename)
            : base_path('docs/Productos img/' . basename($filename));
        abort_unless(is_file($path), 404);
        return response()->file($path);
    }

    public function deleteReferenceImage(Request $request)
    {
        $filename = $request->validate(['filename' => 'required|string|max:255'])['filename'];
        abort_if(str_contains($filename, '..') || basename($filename) !== $filename && ! str_starts_with($filename, 'product-reference/'), 404);
        $path = str_starts_with($filename, 'product-reference/')
            ? storage_path('app/public/' . $filename)
            : base_path('docs/Productos img/' . $filename);
        abort_unless(is_file($path), 404);
        unlink($path);
        Product::where('reference_image', $filename)->update(['reference_image' => null]);
        return response()->json(['success' => true]);
    }

    private function saveReferenceImage(Request $request, array &$validated): void
    {
        if ($request->hasFile('reference_image_file')) {
            $file = $request->file('reference_image_file');
            $directory = base_path('docs/Productos img');
            if (! is_dir($directory)) mkdir($directory, 0755, true);

            $extension = strtolower($file->getClientOriginalExtension());
            $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $name = preg_replace('/[^A-Za-z0-9_-]+/', '-', $name) ?: 'imagen-referencial';
            $filename = $name . '.' . $extension;
            $counter = 1;
            while (is_file($directory . '/' . $filename)) {
                $filename = $name . '-' . $counter++ . '.' . $extension;
            }
            $file->move($directory, $filename);
            $validated['reference_image'] = $filename;
        }
    }

    public function uploadReferenceImage(Request $request)
    {
        $request->validate(['reference_image_file' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120']);
        $validated = [];
        $this->saveReferenceImage($request, $validated);
        return response()->json(['filename' => $validated['reference_image']]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => ['required', 'string', 'max:255', Rule::unique(Product::class)],
            'grams' => 'required|integer|min:0',
            'units_per_box' => 'required|integer|min:1',
            'stock_boxes' => 'required|integer|min:0',
            'min_stock_boxes' => 'required|integer|min:0',
            'production_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'reference_image' => 'nullable|string|max:255',
            'reference_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        $this->saveReferenceImage($request, $validated);

        $product = Product::create($validated);
        AuditService::log('CREACIÓN DE PRODUCTO', "Creó producto: {$validated['name']}", $product);

        return redirect('/productos');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', ['product' => $product, 'referenceImages' => $this->referenceImages()]);
    }

    public function show(Product $product): View
    {
        $product->load('recipes.input');
        $recipeSources = Product::where('status', 'active')->where('id', '!=', $product->id)->whereHas('recipes')->orderBy('name')->get(['id', 'name']);

        return view('products._detail', compact('product', 'recipeSources'));
    }

    public function update(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        try {
            if ($request->ajax()) {
                $rules = [
                    'name' => 'sometimes|required|string|max:255',
                    'sku' => ['sometimes', 'required', 'string', 'max:255', Rule::unique(Product::class)->ignore($product)],
                    'stock_boxes' => 'sometimes|integer|min:0',
                    'min_stock_boxes' => 'sometimes|integer|min:0',
                    'production_cost' => 'sometimes|nullable|numeric|min:0',
                     'status' => 'sometimes|required|in:active,inactive',
                     'reference_image' => 'sometimes|nullable|string|max:255',
                     'reference_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
                ];
            } else {
                $rules = [
                    'name' => 'required|string|max:255',
                    'sku' => ['required', 'string', 'max:255', Rule::unique(Product::class)->ignore($product)],
                    'grams' => 'required|integer|min:0',
                    'units_per_box' => 'required|integer|min:1',
                    'stock_boxes' => 'required|integer|min:0',
                    'min_stock_boxes' => 'required|integer|min:0',
                    'production_cost' => 'nullable|numeric|min:0',
                    'status' => 'required|in:active,inactive',
                    'reference_image' => 'nullable|string|max:255',
                    'reference_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
                ];
            }
            $validated = $request->validate($rules);
            $this->saveReferenceImage($request, $validated);

            $product->update($validated);
            $product->load('recipes.input');
            AuditService::log('ACTUALIZACIÓN DE PRODUCTO', "Actualizó producto: {$product->name}", $product);

            if ($request->ajax()) {
                $margin = 0 - $product->cost_per_box;

                return response()->json([
                    'success' => true,
                    'cost_per_box' => $product->cost_per_box,
                    'production_cost' => $product->production_cost,
                    'margin' => $margin,
                    'margin_pct' => 0,
                    'production_capacity' => $product->production_capacity,
                ]);
            }

            return redirect('/productos');
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Product $product): JsonResponse|RedirectResponse
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

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect('/productos')->with('success', 'Producto eliminado correctamente.');
    }
}
