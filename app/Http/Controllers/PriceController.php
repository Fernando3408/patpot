<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Price;
use App\Models\Product;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PriceController extends Controller
{
    public function index(Request $request)
    {
        $query = Price::with(['customer', 'product']);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $prices = $query->orderBy('customer_id')->get();
        $customers = Customer::where('status', true)->orderBy('business_name')->get();

        return view('prices.index', compact('prices', 'customers'));
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
            'product_id' => ['required', 'exists:products,id', \Illuminate\Validation\Rule::unique('prices')->where(fn ($q) => $q->where('customer_id', $request->input('customer_id'))->whereNull('deleted_at'))],
            'price_box' => 'required|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0',
            'offer_until' => 'nullable|date',
        ]);

        Price::withTrashed()
            ->where('customer_id', $validated['customer_id'])
            ->where('product_id', $validated['product_id'])
            ->forceDelete();

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
                    'product_id' => ['sometimes', 'required', 'exists:products,id', \Illuminate\Validation\Rule::unique('prices')->where(fn ($q) => $q->where('customer_id', $request->input('customer_id') ?? $price->customer_id)->whereNull('deleted_at'))->ignore($price->id)],
                    'price_box' => 'sometimes|required|numeric|min:0',
                    'offer_price' => 'sometimes|nullable|numeric|min:0',
                    'offer_until' => 'sometimes|nullable|date',
                ];
            } else {
                $rules = [
                    'customer_id' => 'required|exists:customers,id',
                    'product_id' => ['required', 'exists:products,id', \Illuminate\Validation\Rule::unique('prices')->where(fn ($q) => $q->where('customer_id', $request->input('customer_id'))->whereNull('deleted_at'))->ignore($price->id)],
                    'price_box' => 'required|numeric|min:0',
                    'offer_price' => 'nullable|numeric|min:0',
                    'offer_until' => 'nullable|date',
                ];
            }
            $validated = $request->validate($rules);

            $price->update($validated);
            AuditService::log('ACTUALIZACIÓN DE PRECIO', 'Actualizó precio', $price);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }

            return redirect('/precios');
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Price $price)
    {
        $price->delete();
        AuditService::log('ELIMINACIÓN DE PRECIO', 'Eliminó precio', $price);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect('/precios');
    }
}
