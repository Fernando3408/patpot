<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

        $customers = $query->orderBy('business_name')->get();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['rut' => $this->normalizeRut($request->input('rut'))]);
        $validated = $request->validate($this->rules());
        $validated['code'] = filled($validated['code'] ?? null) ? $validated['code'] : $this->nextCode();
        $validated['status'] = true;
        $request->merge(['contacts' => array_values(array_filter($request->input('contacts', []), fn (array $contact): bool => filled($contact['name'] ?? null)))]);
        $contacts = $request->validate(['contacts' => ['nullable', 'array'], 'contacts.*.name' => ['required', 'string', 'max:255'], 'contacts.*.phone' => ['nullable', 'string', 'max:50'], 'contacts.*.email' => ['nullable', 'email', 'max:255']])['contacts'] ?? [];

        $customer = Customer::query()->create($validated);
        $customer->contacts()->createMany($contacts);
        AuditService::log('CREACIÓN DE CLIENTE', "Creó cliente: {$customer->business_name}", $customer);

        return redirect()->route('customers.show', $customer);
    }

    private function nextCode(): string
    {
        $lastCode = Customer::withTrashed()->where('code', 'like', 'CLI-%')->orderByDesc('id')->value('code');
        $number = $lastCode && preg_match('/CLI-(\d+)/', $lastCode, $matches) ? (int) $matches[1] : 0;

        return 'CLI-'.str_pad((string) ($number + 1), 4, '0', STR_PAD_LEFT);
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', ['customer' => $customer->load('contacts')]);
    }

    public function show(Request $request, Customer $customer): View
    {
        $customer->load(['stores', 'prices.product', 'contacts', 'orders' => fn ($query) => $query->latest('ordered_on')->limit(10)]);

        return $request->ajax() ? view('customers._detail', compact('customer')) : view('customers.show', compact('customer'));
    }

    public function update(Request $request, Customer $customer): JsonResponse|RedirectResponse
    {
        try {
            $request->merge(['rut' => $this->normalizeRut($request->input('rut'))]);
            $request->merge(['contacts' => array_values(array_filter($request->input('contacts', []), fn (array $contact): bool => filled($contact['name'] ?? null)))]);
            $validated = $request->validate($this->rules($customer, $request->ajax()));
            $contacts = $request->validate(['contacts' => ['nullable', 'array'], 'contacts.*.name' => ['required', 'string', 'max:255'], 'contacts.*.phone' => ['nullable', 'string', 'max:50'], 'contacts.*.email' => ['nullable', 'email', 'max:255']])['contacts'] ?? [];
            $customer->update($validated);
            $customer->contacts()->delete();
            $customer->contacts()->createMany($contacts);
            AuditService::log('ACTUALIZACIÓN DE CLIENTE', "Actualizó cliente: {$customer->business_name}", $customer);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('customers.index');
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        if ($customer->stores()->exists() || $customer->prices()->exists() || $customer->orders()->exists()) {
            return back()->withErrors([
                'delete' => 'No puedes eliminar este cliente porque tiene salas, precios o pedidos asociados.',
            ]);
        }

        $customer->update(['deleted_by' => auth()->id()]);
        $customer->delete();
        AuditService::log('ELIMINACIÓN DE CLIENTE', "Eliminó cliente: {$customer->business_name}", $customer);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('customers.index');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(?Customer $customer = null, bool $isAjax = false): array
    {
        $req = $isAjax ? 'sometimes' : 'required';

        return [
            'code' => [$isAjax ? 'sometimes' : 'nullable', 'string', 'max:100', Rule::unique(Customer::class)->ignore($customer)],
            'business_name' => [$req, 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'rut' => ['nullable', 'string', 'max:12', Rule::unique(Customer::class)->ignore($customer), function (string $attribute, mixed $value, \Closure $fail): void {
                if (filled($value) && ! $this->isValidRut((string) $value)) {
                    $fail('El RUT no es válido.');
                }
            }],
            'type' => ['nullable', 'string', 'max:100'],
            'channel' => ['nullable', 'string', 'max:100'],
            'contact' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    private function normalizeRut(?string $rut): ?string
    {
        if (blank($rut)) {
            return null;
        }
        $clean = strtoupper(preg_replace('/[^0-9Kk]/', '', $rut));
        if (strlen($clean) < 2) {
            return $clean;
        }

        return number_format((int) substr($clean, 0, -1), 0, '', '.').'-'.substr($clean, -1);
    }

    private function isValidRut(string $rut): bool
    {
        $clean = strtoupper(str_replace(['.', '-'], '', $rut));
        if (! preg_match('/^([0-9]+)([0-9K])$/', $clean, $matches)) {
            return false;
        }
        $sum = 0;
        foreach (array_values(str_split(strrev($matches[1]))) as $index => $digit) {
            $sum += (int) $digit * [2, 3, 4, 5, 6, 7][$index % 6];
        }
        $remainder = 11 - ($sum % 11);
        $expected = $remainder === 11 ? '0' : ($remainder === 10 ? 'K' : (string) $remainder);

        return $expected === $matches[2];
    }
}
