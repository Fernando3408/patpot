<?php

namespace Tests\Feature;

use App\Models\Input;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RecipeControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_a_product_recipe_can_be_updated_with_multiple_inputs(): void
    {
        $product = Product::query()->create($this->productData());
        $potato = Input::query()->create($this->inputData());
        $oil = Input::query()->create($this->inputData([
            'code' => 'INS-ACEITE',
            'name' => 'Aceite vegetal',
            'unit' => 'l',
            'unit_cost' => 1_500,
        ]));

        $response = $this->put("/recetas/{$product->id}", [
            'ingredients' => [
                $potato->id => [
                    'input_id' => $potato->id,
                    'selected' => 1,
                    'qty_per_box' => 4.2,
                ],
                $oil->id => [
                    'input_id' => $oil->id,
                    'selected' => 1,
                    'qty_per_box' => 0.65,
                ],
            ],
        ]);

        $response->assertRedirect('/recetas');

        $this->assertSame('4.2000', Recipe::query()
            ->whereBelongsTo($product)
            ->whereBelongsTo($potato, 'input')
            ->value('qty_per_box'));
        $this->assertSame('0.6500', Recipe::query()
            ->whereBelongsTo($product)
            ->whereBelongsTo($oil, 'input')
            ->value('qty_per_box'));
    }

    public function test_an_updated_recipe_removes_unselected_inputs(): void
    {
        $product = Product::query()->create($this->productData());
        $potato = Input::query()->create($this->inputData());
        $oil = Input::query()->create($this->inputData(['code' => 'INS-ACEITE']));

        Recipe::query()->create([
            'product_id' => $product->id,
            'input_id' => $oil->id,
            'qty_per_box' => 0.65,
        ]);

        $this->put("/recetas/{$product->id}", [
            'ingredients' => [
                $potato->id => [
                    'input_id' => $potato->id,
                    'selected' => 1,
                    'qty_per_box' => 4.2,
                ],
                $oil->id => [
                    'input_id' => $oil->id,
                    'qty_per_box' => 0.65,
                ],
            ],
        ])->assertRedirect('/recetas');

        $this->assertSoftDeleted('recipes', [
            'product_id' => $product->id,
            'input_id' => $oil->id,
        ]);
    }

    public function test_a_recipe_requires_at_least_one_selected_input(): void
    {
        $product = Product::query()->create($this->productData());
        $input = Input::query()->create($this->inputData());

        $response = $this->from("/recetas/{$product->id}/edit")
            ->put("/recetas/{$product->id}", [
                'ingredients' => [
                    $input->id => [
                        'input_id' => $input->id,
                        'qty_per_box' => 4.2,
                    ],
                ],
            ]);

        $response->assertRedirect("/recetas/{$product->id}/edit");
        $response->assertSessionHasErrors('ingredients');
    }

    /**
     * @return array<string, int|string>
     */
    private function productData(): array
    {
        return [
            'name' => 'Papas 150 g',
            'sku' => 'PAP-150',
            'grams' => 150,
            'units_per_box' => 12,
            'stock_boxes' => 10,
            'min_stock_boxes' => 5,
            'sale_price_box' => 3_990,
            'status' => 'active',
        ];
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
            'transit' => 10,
            'supplier_id' => null,
            'status' => 1,
        ], $overrides);
    }
}
