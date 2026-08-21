<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query()
            ->withCount('stores');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('trade_name', 'like', "%{$search}%")
                    ->orWhere('rut', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('business_name')->paginate(20)->appends($request->query());

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = Customer::query()->create($request->validate($this->rules()));
        AuditService::log('CREACIÓN DE CLIENTE', "Creó cliente: {$customer->business_name}", $customer);

        return redirect()->route('customers.index');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', ['customer' => $customer]);
    }

    public function show(Customer $customer): View
    {
        $customer->load('stores', 'prices');
        return view('customers._detail', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        try {
            $validated = $request->validate($this->rules($customer, $request->ajax()));
            $customer->update($validated);
            AuditService::log('ACTUALIZACIÓN DE CLIENTE', "Actualizó cliente: {$customer->business_name}", $customer);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect()->route('customers.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->stores()->exists() || $customer->prices()->exists()) {
            return back()->withErrors([
                'delete' => 'No puedes eliminar este cliente porque tiene salas o precios asociados.',
            ]);
        }

        $customer->update(['deleted_by' => auth()->id()]);
        $customer->delete();
        AuditService::log('ELIMINACIÓN DE CLIENTE', "Eliminó cliente: {$customer->business_name}", $customer);

        return redirect()->route('customers.index');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(?Customer $customer = null, bool $isAjax = false): array
    {
        $req = $isAjax ? 'sometimes' : 'required';
        return [
            'code' => [$req, 'string', 'max:100', Rule::unique(Customer::class)->ignore($customer)],
            'business_name' => [$req, 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'rut' => ['nullable', 'string', 'max:20', Rule::unique(Customer::class)->ignore($customer)],
            'type' => ['nullable', 'string', 'max:100'],
            'channel' => ['nullable', 'string', 'max:100'],
            'contact' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'discount' => [$req, 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'boolean'],
        ];
    }
}
