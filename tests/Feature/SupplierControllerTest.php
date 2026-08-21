<?php

namespace Tests\Feature;

use App\Models\Input;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SupplierControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_a_supplier_can_be_created(): void
    {
        $response = $this->post('/proveedores', $this->supplierData());

        $response->assertRedirect('/proveedores');

        $supplier = Supplier::query()->where('email', 'contacto@proveedor.cl')->first();

        $this->assertModelExists($supplier);
        $this->assertSame('Proveedor de prueba', $supplier->name);
    }

    public function test_a_supplier_requires_a_valid_email_address(): void
    {
        $response = $this->from('/proveedores/create')->post('/proveedores', $this->supplierData([
            'email' => 'correo-invalido',
        ]));

        $response->assertRedirect('/proveedores/create');
        $response->assertSessionHasErrors('email');
    }

    public function test_a_supplier_can_be_updated(): void
    {
        $supplier = Supplier::query()->create($this->supplierData());

        $response = $this->put("/proveedores/{$supplier->id}", $this->supplierData([
            'name' => 'Proveedor actualizado',
            'lead_time_days' => 14,
        ]));

        $response->assertRedirect('/proveedores');

        $supplier->refresh();

        $this->assertSame('Proveedor actualizado', $supplier->name);
        $this->assertSame(14, $supplier->lead_time_days);
    }

    public function test_a_supplier_with_inputs_cannot_be_deleted(): void
    {
        $supplier = Supplier::query()->create($this->supplierData());

        Input::query()->create([
            'code' => 'INS-PAPA',
            'name' => 'Papa',
            'unit' => 'kg',
            'supplier_id' => $supplier->id,
        ]);

        $response = $this->from('/proveedores')->delete("/proveedores/{$supplier->id}");

        $response->assertRedirect('/proveedores');
        $response->assertSessionHasErrors('delete');
        $this->assertModelExists($supplier);
    }

    /**
     * @return array<string, int|string>
     */
    private function supplierData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Proveedor de prueba',
            'rut' => '76.123.456-7',
            'contact_name' => 'María Pérez',
            'email' => 'contacto@proveedor.cl',
            'phone' => '+56 9 1234 5678',
            'lead_time_days' => 7,
            'payment_terms' => '30 días',
            'status' => 1,
        ], $overrides);
    }
}
