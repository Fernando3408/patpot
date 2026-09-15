<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Input;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Role;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'administrativo']);
        $user->roles()->attach($role);

        $this->actingAs($user);
    }

    public function test_dispatch_freezes_product_and_variable_cost_per_box(): void
    {
        [$order, $line] = $this->orderWithRecipe();

        app(InventoryService::class)->dispatchOrder($order, [$line->id => 4], today()->toDateString(), [
            'freight_cost' => 8000,
            'management_cost' => 4000,
            'other_cost' => 0,
        ]);

        $shipmentLine = $order->shipments()->firstOrFail()->lines()->firstOrFail();

        $this->assertSame('1000.00', $shipmentLine->cost_box);
        $this->assertSame('3000.00', $shipmentLine->variable_cost_box);
    }

    public function test_order_lines_cannot_be_reduced_below_already_dispatched_boxes(): void
    {
        [$order, $line] = $this->orderWithRecipe(['boxes' => 10]);
        $line->update(['dispatched_boxes' => 6]);

        $this->expectException(ValidationException::class);

        try {
            app(InventoryService::class)->updateOrderLines($order, [[
                'id' => $line->id,
                'product_id' => $line->product_id,
                'boxes' => 5,
                'price_box' => $line->price_box,
            ]]);
        } finally {
            $this->assertSame(10, $line->fresh()->boxes);
        }
    }

    public function test_dispatch_total_uses_price_box_regardless_of_legacy_discount(): void
    {
        [$order, $line] = $this->orderWithRecipe([
            'boxes' => 4,
            'price_box' => 5000,
        ]);

        app(InventoryService::class)->dispatchOrder($order, [$line->id => 4], today()->toDateString());

        $this->assertSame('20000.00', $order->shipments()->firstOrFail()->total);
    }

    /**
     * @param  array<string, mixed>  $lineOverrides
     * @return array{0: Order, 1: OrderLine}
     */
    private function orderWithRecipe(array $lineOverrides = []): array
    {
        $customer = Customer::query()->create([
            'code' => 'CLI-TEST',
            'business_name' => 'Cliente Test',
            'status' => true,
        ]);
        $input = Input::query()->create([
            'code' => 'INS-TEST',
            'name' => 'Insumo Test',
            'category' => 'Materia prima',
            'unit' => 'kg',
            'stock' => 100,
            'safety_stock' => 10,
            'weekly_consumption' => 10,
            'lead_time_days' => 7,
            'target_weeks' => 4,
            'min_purchase' => 1,
            'purchase_multiple' => 1,
            'unit_cost' => 500,
            'transit' => 0,
            'status' => true,
        ]);
        $product = Product::query()->create([
            'sku' => 'PROD-TEST',
            'name' => 'Producto Test',
            'grams' => 150,
            'units_per_box' => 12,
            'stock_boxes' => 20,
            'min_stock_boxes' => 1,
            'sale_price_box' => 5000,
            'status' => 'active',
        ]);
        Recipe::query()->create(['product_id' => $product->id, 'input_id' => $input->id, 'qty_per_box' => 2]);

        $order = Order::query()->create([
            'number' => 'PED-TEST-001',
            'customer_id' => $customer->id,
            'ordered_on' => today(),
            'delivery_on' => today()->addDay(),
        ]);
        $line = $order->lines()->create(array_merge([
            'product_id' => $product->id,
            'boxes' => 4,
            'price_box' => 5000,
        ], $lineOverrides));

        return [$order, $line];
    }
}
