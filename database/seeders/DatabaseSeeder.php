<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Input;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
use App\Models\Production;
use App\Models\Purchase;
use App\Models\Recipe;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(InventoryService $inventoryService): void
    {
        User::query()->firstOrCreate(['email' => 'admin@patpot.cl'], [
            'name' => 'Administrador PatPot',
            'password' => 'password',
        ]);

        $supplier = Supplier::query()->firstOrCreate(['rut' => '76.123.456-7'], [
            'name' => 'Alimentos del Sur SpA', 'contact_name' => 'Carolina Soto', 'email' => 'ventas@alimentosdelsur.cl', 'phone' => '+56 9 5555 1111', 'lead_time_days' => 5, 'payment_terms' => '30 días', 'status' => true,
        ]);

        $inputs = [
            ['code' => 'MP-PAPA', 'name' => 'Papa seleccionada', 'unit' => 'kg', 'category' => 'Materia prima', 'unit_cost' => 950, 'stock' => 0, 'transit' => 1200],
            ['code' => 'MP-ACEITE', 'name' => 'Aceite vegetal alto oleico', 'unit' => 'l', 'category' => 'Materia prima', 'unit_cost' => 1800, 'stock' => 0, 'transit' => 180],
            ['code' => 'MP-SAL', 'name' => 'Sal de mar fina', 'unit' => 'kg', 'category' => 'Condimento', 'unit_cost' => 620, 'stock' => 0, 'transit' => 70],
        ];
        foreach ($inputs as $inputData) {
            Input::query()->firstOrCreate(['code' => $inputData['code']], array_merge($inputData, ['safety_stock' => 50, 'weekly_consumption' => 120, 'lead_time_days' => 5, 'target_weeks' => 4, 'min_purchase' => 42, 'purchase_multiple' => 42, 'supplier_id' => $supplier->id, 'status' => true]));
        }
        $potato = Input::query()->where('code', 'MP-PAPA')->firstOrFail();
        $oil = Input::query()->where('code', 'MP-ACEITE')->firstOrFail();
        $salt = Input::query()->where('code', 'MP-SAL')->firstOrFail();

        foreach ([[$potato, 1200, 950, 'OC-DEMO-001'], [$oil, 180, 1800, 'OC-DEMO-002'], [$salt, 70, 620, 'OC-DEMO-003']] as [$input, $quantity, $cost, $number]) {
            $purchase = Purchase::query()->firstOrCreate(['number' => $number], ['supplier_id' => $supplier->id, 'ordered_on' => today()->subDays(7), 'expected_on' => today()->subDays(2), 'notes' => 'Dato DEMO para pruebas']);
            $line = $purchase->lines()->firstOrCreate(['input_id' => $input->id], ['ordered_quantity' => $quantity, 'unit_cost' => $cost]);
            if ($purchase->fresh()->status !== 'received') {
                $inventoryService->receivePurchase($purchase, [$line->id => $quantity]);
            }
        }

        $products = [];
        foreach ([['PAP-150-SAL', 'Papas Chips Sal de Mar 150 g', 150, 12, 3990], ['PAP-150-MER', 'Papas Chips Merkén 150 g', 150, 12, 4290], ['PAP-045-SAL', 'Papas Chips Sal de Mar 45 g', 45, 42, 2490]] as [$sku, $name, $grams, $units, $price]) {
            $products[$sku] = Product::query()->firstOrCreate(['sku' => $sku], ['name' => $name, 'grams' => $grams, 'units_per_box' => $units, 'stock_boxes' => 0, 'min_stock_boxes' => 20, 'sale_price_box' => $price, 'status' => 'active']);
        }
        foreach ($products as $product) {
            Recipe::query()->updateOrCreate(['product_id' => $product->id, 'input_id' => $potato->id], ['qty_per_box' => 1.4]);
            Recipe::query()->updateOrCreate(['product_id' => $product->id, 'input_id' => $oil->id], ['qty_per_box' => 0.15]);
            Recipe::query()->updateOrCreate(['product_id' => $product->id, 'input_id' => $salt->id], ['qty_per_box' => 0.025]);
        }

        foreach ([['CLI-JUMBO', 'Cencosud Retail S.A.', 'Jumbo', '76.345.678-9', 'Jumbo Ñuñoa', 'SAL-NU'], ['CLI-UNIMARC', 'SMU S.A.', 'Unimarc', '76.456.789-0', 'Unimarc Providencia', 'UNI-PRO'], ['CLI-GOURMET', 'Distribuidora Gourmet SpA', 'Mercado Gourmet', '76.567.890-1', 'Mercado Gourmet Vitacura', 'MG-VIT']] as [$code, $businessName, $tradeName, $rut, $storeName, $storeCode]) {
            $customer = Customer::query()->updateOrCreate(['code' => $code], ['business_name' => $businessName, 'trade_name' => $tradeName, 'rut' => $rut, 'type' => 'Retail', 'channel' => 'Retail', 'contact' => 'Equipo de compras', 'email' => 'compras@demo.cl', 'payment_terms' => '30 días', 'discount' => 2, 'status' => true]);
            Store::query()->updateOrCreate(['code' => $storeCode], ['customer_id' => $customer->id, 'name' => $storeName, 'city' => 'Santiago', 'region' => 'Metropolitana', 'status' => true]);
            foreach ($products as $product) {
                Price::query()->updateOrCreate(['customer_id' => $customer->id, 'product_id' => $product->id], ['price_box' => $product->sale_price_box, 'offer_price' => $product->sale_price_box - 200, 'offer_until' => today()->addMonth()]);
            }
        }

        foreach ($products as $index => $product) {
            $production = Production::query()->firstOrCreate(['number' => 'OP-DEMO-'.str_pad((string) (array_search($index, array_keys($products), true) + 1), 3, '0', STR_PAD_LEFT)], ['product_id' => $product->id, 'planned_boxes' => 100, 'planned_on' => today()->subDays(2), 'notes' => 'Producción DEMO']);
            if ($production->fresh()->status !== 'closed') {
                $inventoryService->closeProduction($production, 100, today()->subDay()->toDateString());
            }
        }

        foreach (Customer::query()->whereIn('code', ['CLI-JUMBO', 'CLI-UNIMARC', 'CLI-GOURMET'])->get() as $position => $customer) {
            $product = $products[array_keys($products)[$position]];
            $store = $customer->stores()->firstOrFail();
            $order = Order::query()->firstOrCreate(['number' => 'PED-DEMO-'.str_pad((string) ($position + 1), 3, '0', STR_PAD_LEFT)], ['customer_id' => $customer->id, 'store_id' => $store->id, 'ordered_on' => today()->subDay(), 'delivery_on' => today()->addDays(3), 'notes' => 'Pedido DEMO']);
            $line = $order->lines()->firstOrCreate(['product_id' => $product->id], ['boxes' => 30, 'price_box' => $product->sale_price_box - 200, 'discount_pct' => 2]);
            if ($order->fresh()->status === 'pending') {
                $inventoryService->dispatchOrder($order, [$line->id => 10], today()->toDateString());
            }
        }
    }
}
