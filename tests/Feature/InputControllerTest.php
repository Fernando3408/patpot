<?php

namespace Tests\Feature;

use App\Models\Input;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class InputControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_an_input_can_be_created(): void
    {
        $response = $this->post('/insumos', $this->inputData());

        $response->assertRedirect('/insumos');

        $input = Input::query()->where('code', 'INS-PAPA')->first();

        $this->assertModelExists($input);
        $this->assertSame('Papa seleccionada', $input->name);
    }

    public function test_an_input_code_must_be_unique(): void
    {
        Input::query()->create($this->inputData());

        $response = $this->from('/insumos/create')->post('/insumos', $this->inputData());

        $response->assertRedirect('/insumos/create');
        $response->assertSessionHasErrors('code');
    }

    public function test_an_input_edit_page_is_available_and_the_input_can_be_updated(): void
    {
        $input = Input::query()->create($this->inputData());

        $this->get("/insumos/{$input->id}/edit")
            ->assertOk()
            ->assertSee('Editar insumo');

        $response = $this->put("/insumos/{$input->id}", $this->inputData([
            'name' => 'Papa lavada',
            'code' => 'INS-PAPA-LAVADA',
            'stock' => 125.5,
        ]));

        $response->assertRedirect('/insumos');

        $input->refresh();

        $this->assertSame('Papa lavada', $input->name);
        $this->assertSame('INS-PAPA-LAVADA', $input->code);
        $this->assertSame('125.50', $input->stock);
    }

    public function test_an_input_used_in_a_recipe_cannot_be_deleted(): void
    {
        $input = Input::query()->create($this->inputData());
        $product = Product::query()->create([
            'sku' => 'PAP-150',
            'name' => 'Papas 150 g',
            'grams' => 150,
            'units_per_box' => 12,
            'stock_boxes' => 0,
            'min_stock_boxes' => 0,
            'sale_price_box' => 3_990,
            'status' => 'active',
        ]);

        Recipe::query()->create([
            'product_id' => $product->id,
            'input_id' => $input->id,
            'qty_per_box' => 1.25,
        ]);

        $response = $this->from('/insumos')->delete("/insumos/{$input->id}");

        $response->assertRedirect('/insumos');
        $response->assertSessionHasErrors('delete');
        $this->assertModelExists($input);
    }

    public function test_a_packaged_kilogram_unit_displays_whole_quantities_without_database_decimals(): void
    {
        $input = Input::query()->create($this->inputData([
            'unit' => '1 kg',
            'stock' => 10,
        ]));

        $this->assertSame('10', $input->formattedQuantity(10));
    }

    /**
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
