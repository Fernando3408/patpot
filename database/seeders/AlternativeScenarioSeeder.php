<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Input;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Role;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class AlternativeScenarioSeeder extends Seeder
{
    public function run(InventoryService $inventory): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@patpot.cl'], ['name' => 'Administrador PatPot', 'password' => 'password', 'status' => true]);
        $admin->roles()->sync([Role::firstOrCreate(['name' => 'admin'])->id]);
        foreach (['ventas', 'produccion', 'administrativo'] as $role) Role::firstOrCreate(['name' => $role]);

        $supplier = Supplier::create(['name' => 'Abastecimientos Cordillera SpA', 'rut' => '77.123.456-0', 'contact_name' => 'Nicolás Vera', 'email' => 'compras@cordillera.test', 'phone' => '+56 9 8888 1111', 'lead_time_days' => 4, 'payment_terms' => '30 días', 'status' => true]);
        $potato = Input::create(['code' => 'ALT-PAPA', 'name' => 'Papa rústica cordillera', 'category' => 'Materia prima', 'type' => 'material', 'unit' => 'kg', 'unit_cost' => 1450, 'stock' => 0, 'transit' => 0, 'safety_stock' => 100, 'weekly_consumption' => 80, 'lead_time_days' => 4, 'target_weeks' => 4, 'min_purchase' => 100, 'purchase_multiple' => 50, 'supplier_id' => $supplier->id, 'status' => true]);
        $bag = Input::create(['code' => 'ALT-BOLSA', 'name' => 'Bolsa kraft 150 g', 'category' => 'Envases', 'type' => 'material', 'unit' => 'unidad', 'unit_cost' => 65, 'stock' => 0, 'transit' => 0, 'safety_stock' => 120, 'weekly_consumption' => 100, 'lead_time_days' => 4, 'target_weeks' => 4, 'min_purchase' => 200, 'purchase_multiple' => 100, 'supplier_id' => $supplier->id, 'status' => true]);
        $product = Product::create(['sku' => 'ALT-150-RUST', 'name' => 'Papas Rústicas Ahumadas 150 g', 'grams' => 150, 'units_per_box' => 12, 'stock_boxes' => 0, 'min_stock_boxes' => 8, 'status' => 'active']);
        Recipe::create(['product_id' => $product->id, 'input_id' => $potato->id, 'qty_per_box' => 1.8]);
        Recipe::create(['product_id' => $product->id, 'input_id' => $bag->id, 'qty_per_box' => 12]);

        $customer = Customer::create(['business_name' => 'Mercado Nuevo Horizonte Ltda.', 'trade_name' => 'Nuevo Horizonte', 'rut' => '76.000.001-9', 'channel' => 'Tienda especializada', 'payment_terms' => '30 días', 'status' => true, 'code' => 'CLI-ALT-01']);
        Store::create(['customer_id' => $customer->id, 'code' => 'ALT-PROV', 'name' => 'Sala Providencia', 'city' => 'Santiago', 'region' => 'Metropolitana', 'status' => true]);
        Price::create(['customer_id' => $customer->id, 'product_id' => $product->id, 'price_box' => 8900]);

        $purchase = $supplier->purchases()->create(['number' => 'ALT-OC-001', 'ordered_on' => now()->subDays(3)->toDateString(), 'expected_on' => now()->toDateString(), 'status' => 'pending']);
        $purchase->lines()->createMany([['input_id' => $potato->id, 'ordered_quantity' => 1000, 'unit_cost' => 1450], ['input_id' => $bag->id, 'ordered_quantity' => 2000, 'unit_cost' => 65]]);
        $inventory->receivePurchase($purchase->fresh(), $purchase->lines->pluck('ordered_quantity', 'id')->all(), now()->toDateString());

        $order = Order::create(['number' => 'ALT-PED-001', 'customer_id' => $customer->id, 'ordered_on' => now()->toDateString(), 'delivery_on' => now()->addDays(5)->toDateString(), 'status' => 'pending']);
        $order->lines()->create(['product_id' => $product->id, 'boxes' => 20, 'price_box' => 8900, 'cost_box' => $product->cost_per_box]);
        $inventory->syncOrderInputConsumptions($order->fresh('lines'));
    }
}
