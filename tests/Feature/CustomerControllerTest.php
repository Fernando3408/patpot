<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_customer_pages_are_available(): void
    {
        $this->get('/clientes')
            ->assertOk()
            ->assertSee('Clientes');

        $this->get('/clientes/create')
            ->assertOk()
            ->assertSee('Nuevo cliente');
    }

    public function test_a_customer_can_be_created_with_its_operational_profile(): void
    {
        $response = $this->post('/clientes', $this->customerData());

        $response->assertRedirect('/clientes');

        $customer = Customer::query()->where('code', 'CLI-CENC')->first();

        $this->assertModelExists($customer);
        $this->assertSame('Cencosud', $customer->trade_name);
        $this->assertSame('Compras retail', $customer->contact);
        $this->assertSame('60 días', $customer->payment_terms);
    }

    public function test_customer_code_and_rut_must_be_unique(): void
    {
        Customer::query()->create($this->customerData());

        $this->from('/clientes/create')
            ->post('/clientes', $this->customerData([
                'rut' => '77.000.000-0',
            ]))
            ->assertRedirect('/clientes/create')
            ->assertSessionHasErrors('code');

        $this->from('/clientes/create')
            ->post('/clientes', $this->customerData([
                'code' => 'CLI-SMU',
            ]))
            ->assertRedirect('/clientes/create')
            ->assertSessionHasErrors('rut');
    }

    public function test_a_customer_can_be_updated_without_changing_its_identifiers(): void
    {
        $customer = Customer::query()->create($this->customerData());

        $response = $this->put("/clientes/{$customer->id}", $this->customerData([
            'trade_name' => 'Jumbo',
            'payment_terms' => '30 días',
        ]));

        $response->assertRedirect('/clientes');

        $customer->refresh();

        $this->assertSame('Jumbo', $customer->trade_name);
        $this->assertSame('30 días', $customer->payment_terms);
    }

    public function test_a_customer_with_stores_cannot_be_deleted(): void
    {
        $customer = Customer::query()->create($this->customerData());

        Store::query()->create([
            'customer_id' => $customer->id,
            'code' => 'JUM-001',
            'name' => 'Jumbo La Dehesa',
            'status' => true,
        ]);

        $this->from('/clientes')
            ->delete("/clientes/{$customer->id}")
            ->assertRedirect('/clientes')
            ->assertSessionHasErrors('delete');

        $this->assertModelExists($customer);
    }

    /**
     * @return array<string, int|string>
     */
    private function customerData(array $overrides = []): array
    {
        return array_merge([
            'code' => 'CLI-CENC',
            'business_name' => 'Cencosud Retail S.A.',
            'trade_name' => 'Cencosud',
            'rut' => '76.000.001-1',
            'type' => 'Supermercado',
            'channel' => 'Retail',
            'contact' => 'Compras retail',
            'email' => 'compras@cencosud.cl',
            'payment_terms' => '60 días',
            'discount' => 0,
            'status' => 1,
        ], $overrides);
    }
}
