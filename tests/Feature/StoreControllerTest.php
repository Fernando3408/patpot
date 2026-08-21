<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_a_store_can_be_created(): void
    {
        $customer = $this->createCustomer();
        $response = $this->post('/salas', $this->storeData($customer));

        $response->assertRedirect('/salas');

        $store = Store::query()->where('code', 'JUM-001')->first();

        $this->assertModelExists($store);
        $this->assertSame('Jumbo La Dehesa', $store->name);
    }

    public function test_a_store_code_must_be_unique_for_the_same_customer(): void
    {
        $customer = $this->createCustomer();
        Store::query()->create($this->storeData($customer));

        $response = $this->from('/salas/create')->post('/salas', $this->storeData($customer));

        $response->assertRedirect('/salas/create');
        $response->assertSessionHasErrors('code');
    }

    public function test_a_store_edit_page_is_available_and_the_store_can_be_updated(): void
    {
        $customer = $this->createCustomer();
        $store = Store::query()->create($this->storeData($customer));

        $this->get("/salas/{$store->id}/edit")
            ->assertOk()
            ->assertSee('Editar sala');

        $response = $this->put("/salas/{$store->id}", $this->storeData($customer, [
            'code' => 'JUM-002',
            'name' => 'Jumbo Costanera',
        ]));

        $response->assertRedirect('/salas');

        $store->refresh();

        $this->assertSame('JUM-002', $store->code);
        $this->assertSame('Jumbo Costanera', $store->name);
    }

    public function test_a_store_without_retail_records_can_be_deleted(): void
    {
        $customer = $this->createCustomer();
        $store = Store::query()->create($this->storeData($customer));

        $response = $this->delete("/salas/{$store->id}");

        $response->assertRedirect('/salas');
        $this->assertSoftDeleted($store);
    }

    private function createCustomer(): Customer
    {
        return Customer::query()->create([
            'code' => 'CLI-PRUEBA',
            'business_name' => 'Cliente de prueba SpA',
            'trade_name' => 'Cliente de prueba',
            'discount' => 0,
            'status' => true,
        ]);
    }

    /**
     * @return array<string, int|string|null>
     */
    private function storeData(Customer $customer, array $overrides = []): array
    {
        return array_merge([
            'customer_id' => $customer->id,
            'code' => 'JUM-001',
            'name' => 'Jumbo La Dehesa',
            'city' => 'Santiago',
            'region' => 'Metropolitana',
            'status' => 1,
        ], $overrides);
    }
}
