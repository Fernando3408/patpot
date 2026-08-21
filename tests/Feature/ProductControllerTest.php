<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_a_product_can_be_created(): void
    {
        $response = $this->post('/productos', $this->productData());

        $response->assertRedirect('/productos');

        $product = Product::query()->where('sku', 'PAP-150')->first();

        $this->assertModelExists($product);
        $this->assertSame('Papas 150 g', $product->name);
    }

    public function test_a_product_sku_must_be_unique(): void
    {
        Product::query()->create($this->productData());

        $response = $this->from('/productos/create')->post('/productos', $this->productData());

        $response->assertRedirect('/productos/create');
        $response->assertSessionHasErrors('sku');
    }

    public function test_a_product_can_be_updated(): void
    {
        $product = Product::query()->create($this->productData());

        $response = $this->put("/productos/{$product->id}", $this->productData([
            'name' => 'Papas 150 g con merkén',
            'sku' => 'PAP-150-MERKEN',
        ]));

        $response->assertRedirect('/productos');

        $product->refresh();

        $this->assertSame('Papas 150 g con merkén', $product->name);
        $this->assertSame('PAP-150-MERKEN', $product->sku);
    }

    public function test_a_product_with_a_customer_price_cannot_be_deleted(): void
    {
        $product = Product::query()->create($this->productData());
        $customer = Customer::query()->create([
            'code' => 'CLI-PRUEBA',
            'business_name' => 'Cliente de prueba',
            'discount' => 0,
            'status' => true,
        ]);

        Price::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'price_box' => 3_990,
        ]);

        $response = $this->from('/productos')->delete("/productos/{$product->id}");

        $response->assertRedirect('/productos');
        $response->assertSessionHasErrors('delete');
        $this->assertModelExists($product);
    }

    /**
     * @return array<string, int|string>
     */
    private function productData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Papas 150 g',
            'sku' => 'PAP-150',
            'grams' => 150,
            'units_per_box' => 12,
            'stock_boxes' => 10,
            'min_stock_boxes' => 5,
            'sale_price_box' => 3_990,
            'status' => 'active',
        ], $overrides);
    }
}
