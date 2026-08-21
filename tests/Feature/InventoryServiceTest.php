<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Input;
use App\Models\Product;
use App\Models\Production;
use App\Models\Recipe;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_projected_stock_subtracts_open_production_commitments(): void
    {
        $input = Input::query()->create($this->inputData(['stock' => 100, 'transit' => 30]));
        $product = Product::query()->create($this->productData());
        Recipe::query()->create(['product_id' => $product->id, 'input_id' => $input->id, 'qty_per_box' => 2]);
        Production::query()->create(['number' => 'OP-0001', 'product_id' => $product->id, 'planned_boxes' => 10, 'planned_on' => today()]);

        $input->load('recipes.product.productions');

        $this->assertSame(20.0, $input->committed_quantity);
        $this->assertSame(110.0, $input->projected_stock);
    }

    public function test_closing_production_rejects_fractional_boxes(): void
    {
        $input = Input::query()->create($this->inputData(['stock' => 10]));
        $product = Product::query()->create($this->productData(['stock_boxes' => 0]));
        Recipe::query()->create(['product_id' => $product->id, 'input_id' => $input->id, 'qty_per_box' => 1]);
        $production = Production::query()->create(['number' => 'OP-0002', 'product_id' => $product->id, 'planned_boxes' => 2, 'planned_on' => today()]);

        $this->from('/produccion')->post("/produccion/{$production->id}/cerrar", [
            'actual_boxes' => 1.5,
            'completed_on' => today()->toDateString(),
        ])->assertRedirect('/produccion')->assertSessionHasErrors('actual_boxes');

        $this->assertSame(0, $product->fresh()->stock_boxes);
    }

    public function test_order_cannot_use_a_store_belonging_to_another_customer(): void
    {
        $customer = Customer::query()->create($this->customerData('CLI-001'));
        $otherCustomer = Customer::query()->create($this->customerData('CLI-002'));
        $store = Store::query()->create(['customer_id' => $otherCustomer->id, 'code' => 'SAL-001', 'name' => 'Sala ajena', 'status' => true]);
        $product = Product::query()->create($this->productData());

        $this->from('/pedidos/create')->post('/pedidos', [
            'number' => 'PED-0001',
            'customer_id' => $customer->id,
            'store_id' => $store->id,
            'ordered_on' => today()->toDateString(),
            'lines' => [['product_id' => $product->id, 'boxes' => 1, 'price_box' => 3_990]],
        ])->assertRedirect('/pedidos/create')->assertSessionHasErrors('store_id');
    }

    /**
     * @param  array<string, float|int|string|null>  $overrides
     * @return array<string, float|int|string|null>
     */
    private function inputData(array $overrides = []): array
    {
        return array_merge([
            'code' => 'INS-PAPA',
            'name' => 'Papa seleccionada',
            'category' => 'Materia prima',
            'unit' => 'kg',
            'stock' => 100,
            'safety_stock' => 20,
            'weekly_consumption' => 50,
            'lead_time_days' => 7,
            'target_weeks' => 4,
            'min_purchase' => 42,
            'purchase_multiple' => 42,
            'unit_cost' => 950,
            'transit' => 0,
            'supplier_id' => null,
            'status' => true,
        ], $overrides);
    }

    /**
     * @param  array<string, float|int|string>  $overrides
     * @return array<string, float|int|string>
     */
    private function productData(array $overrides = []): array
    {
        return array_merge([
            'sku' => 'PAP-150',
            'name' => 'Papas 150 g',
            'grams' => 150,
            'units_per_box' => 12,
            'stock_boxes' => 0,
            'min_stock_boxes' => 0,
            'sale_price_box' => 3_990,
            'status' => 'active',
        ], $overrides);
    }

    /**
     * @return array<string, float|int|string>
     */
    private function customerData(string $code): array
    {
        return [
            'code' => $code,
            'business_name' => "Cliente {$code}",
            'discount' => 0,
            'status' => true,
        ];
    }
}
