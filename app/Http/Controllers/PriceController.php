<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Price;
use App\Models\Product;
use App\Services\AuditService;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function index(Request $request)
    {
        $query = Price::with(['customer', 'product']);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $prices = $query->orderBy('customer_id')->get();

        return view('prices.index', compact('prices'));
    }

    public function create()
    {
        $customers = Customer::where('status', true)->get();
        $products = Product::where('status', 'active')->get();

        return view('prices.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'price_box' => 'required|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0',
            'offer_until' => 'nullable|date',
        ]);

        $exists = Price::where('customer_id', $validated['customer_id'])
            ->where('product_id', $validated['product_id'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'product_id' => 'Ya existe un precio para este producto y este cliente. Edítalo en vez de crear uno nuevo.',
                ])
                ->withInput();
        }

        $price = Price::create($validated);
        AuditService::log('CREACIÓN DE PRECIO', 'Creó precio para cliente', $price);

        return redirect('/precios');
    }

    public function edit(Price $price)
    {
        $customers = Customer::where('status', true)->get();
        $products = Product::where('status', 'active')->get();

        return view('prices.edit', compact('price', 'customers', 'products'));
    }

    public function show(Price $price)
    {
        $price->load('customer', 'product');
        return view('prices._detail', compact('price'));
    }

    public function update(Request $request, Price $price)
    {
        try {
            if ($request->ajax()) {
                $rules = [
                    'customer_id' => 'sometimes|required|exists:customers,id',
                    'product_id' => 'sometimes|required|exists:products,id',
                    'price_box' => 'sometimes|required|numeric|min:0',
                    'offer_price' => 'sometimes|nullable|numeric|min:0',
                    'offer_until' => 'sometimes|nullable|date',
                ];
            } else {
                $rules = [
                    'customer_id' => 'required|exists:customers,id',
                    'product_id' => 'required|exists:products,id',
                    'price_box' => 'required|numeric|min:0',
                    'offer_price' => 'nullable|numeric|min:0',
                    'offer_until' => 'nullable|date',
                ];
            }
            $validated = $request->validate($rules);

            $checkCustomerId = $validated['customer_id'] ?? $price->customer_id;
            $checkProductId = $validated['product_id'] ?? $price->product_id;

            $exists = Price::where('customer_id', $checkCustomerId)
                ->where('product_id', $checkProductId)
                ->where('id', '!=', $price->id)
                ->exists();

            if ($exists) {
                if ($request->ajax()) {
                    return response()->json(['errors' => ['product_id' => ['Ya existe un precio para este producto y este cliente.']]], 422);
                }
                return back()
                    ->withErrors([
                        'product_id' => 'Ya existe un precio para este producto y este cliente.',
                    ])
                    ->withInput();
            }

            $price->update($validated);
            AuditService::log('ACTUALIZACIÓN DE PRECIO', 'Actualizó precio', $price);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect('/precios');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(Price $price)
    {
        $price->delete();
        AuditService::log('ELIMINACIÓN DE PRECIO', 'Eliminó precio', $price);

        return redirect('/precios');
    }
}
