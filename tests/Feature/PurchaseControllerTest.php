<?php

namespace Tests\Feature;

use App\Models\Input;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PurchaseControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_receiving_a_purchase_updates_current_input_cost(): void
    {
        $supplier = Supplier::query()->create($this->supplierData());
        $input = Input::query()->create($this->inputData(['supplier_id' => $supplier->id, 'unit_cost' => 4000]));
        $purchase = Purchase::query()->create([
            'number' => 'OC-COST-001',
            'supplier_id' => $supplier->id,
            'ordered_on' => today(),
            'expected_on' => today(),
        ]);
        $line = $purchase->lines()->create([
            'input_id' => $input->id,
            'ordered_quantity' => 10,
            'unit_cost' => 5000,
        ]);

        app(InventoryService::class)->receivePurchase($purchase, [$line->id => 10], today()->toDateString());

        $this->assertSame('5000.00', $input->fresh()->unit_cost);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function supplierData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Proveedor Envases',
            'rut' => '76.111.111-1',
            'lead_time_days' => 7,
            'status' => true,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function inputData(array $overrides = []): array
    {
        return array_merge([
            'code' => 'ENV-001',
            'name' => 'Envase grande',
            'category' => 'Envases',
            'unit' => 'caja',
            'stock' => 0,
            'safety_stock' => 10,
            'weekly_consumption' => 10,
            'lead_time_days' => 7,
            'target_weeks' => 4,
            'min_purchase' => 1,
            'purchase_multiple' => 1,
            'unit_cost' => 4000,
            'transit' => 0,
            'supplier_id' => null,
            'status' => true,
        ], $overrides);
    }
}
