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
use App\Models\Task;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(InventoryService $inventoryService): void
    {
        $admin = User::query()->firstOrCreate(['email' => 'admin@patpot.cl'], [
            'name' => 'Administrador PatPot',
            'password' => 'password',
        ]);
        $adminRole = \App\Models\Role::query()->firstOrCreate(['name' => 'admin']);
        if (! $admin->roles()->where('role_id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole);
        }

        $operador = User::query()->firstOrCreate(['email' => 'operador@patpot.cl'], [
            'name' => 'María López',
            'password' => 'password',
        ]);
        $operadorRole = \App\Models\Role::query()->firstOrCreate(['name' => 'operador']);
        if (! $operador->roles()->where('role_id', $operadorRole->id)->exists()) {
            $operador->roles()->attach($operadorRole);
        }

        $this->seedSuppliers();
        $this->seedInputs();
        $this->seedProducts();
        $this->seedCustomers();
        $this->seedPurchasesAndReceive($inventoryService);
        $this->seedProductions($inventoryService);
        $this->seedOrders($inventoryService);
        $this->seedRetail();
        $this->seedTasks();

        $this->call(ChatKnowledgeSeeder::class);
    }

    private function seedSuppliers(): void
    {
        $suppliers = [
            ['name' => 'AgroPapa Sur SpA', 'rut' => '76.111.222-3', 'contact_name' => 'Carlos Muñoz', 'email' => 'ventas@agropapa.cl', 'phone' => '+56 9 7777 1010', 'lead_time_days' => 3, 'payment_terms' => '30 días', 'status' => true],
            ['name' => 'Aceites del Valle Ltda', 'rut' => '76.333.444-5', 'contact_name' => 'Patricia Vega', 'email' => 'pedidos@aceitesvalle.cl', 'phone' => '+56 9 6666 2020', 'lead_time_days' => 5, 'payment_terms' => '45 días', 'status' => true],
            ['name' => 'Empaques Kraft SpA', 'rut' => '76.555.666-7', 'contact_name' => 'Andrés Figueroa', 'email' => 'ventas@empaqueskraft.cl', 'phone' => '+56 9 5555 3030', 'lead_time_days' => 7, 'payment_terms' => '60 días', 'status' => true],
        ];
        foreach ($suppliers as $data) {
            Supplier::query()->firstOrCreate(['rut' => $data['rut']], $data);
        }
    }

    private function seedInputs(): void
    {
        $s1 = Supplier::query()->where('rut', '76.111.222-3')->firstOrFail();
        $s2 = Supplier::query()->where('rut', '76.333.444-5')->firstOrFail();
        $s3 = Supplier::query()->where('rut', '76.555.666-7')->firstOrFail();

        $inputs = [
            ['code' => 'MP-PAPA-01', 'name' => 'Papa shiny pink', 'category' => 'Materia prima', 'type' => 'material', 'unit' => 'kg', 'unit_cost' => 1100, 'stock' => 0, 'transit' => 5500, 'safety_stock' => 800, 'weekly_consumption' => 950, 'lead_time_days' => 3, 'target_weeks' => 4, 'min_purchase' => 500, 'purchase_multiple' => 500, 'supplier_id' => $s1->id],
            ['code' => 'MP-PAPA-02', 'name' => 'Papa crits', 'category' => 'Materia prima', 'type' => 'material', 'unit' => 'kg', 'unit_cost' => 1200, 'stock' => 0, 'transit' => 2000, 'safety_stock' => 600, 'weekly_consumption' => 700, 'lead_time_days' => 3, 'target_weeks' => 4, 'min_purchase' => 500, 'purchase_multiple' => 500, 'supplier_id' => $s1->id],
            ['code' => 'MP-ACEITE', 'name' => 'Aceite vegetal alto oleico', 'category' => 'Materia prima', 'type' => 'material', 'unit' => 'l', 'unit_cost' => 1800, 'stock' => 0, 'transit' => 400, 'safety_stock' => 100, 'weekly_consumption' => 180, 'lead_time_days' => 5, 'target_weeks' => 4, 'min_purchase' => 200, 'purchase_multiple' => 200, 'supplier_id' => $s2->id],
            ['code' => 'MP-SAL', 'name' => 'Sal de mar fina', 'category' => 'Condimento', 'type' => 'material', 'unit' => 'kg', 'unit_cost' => 620, 'stock' => 0, 'transit' => 100, 'safety_stock' => 40, 'weekly_consumption' => 35, 'lead_time_days' => 4, 'target_weeks' => 6, 'min_purchase' => 50, 'purchase_multiple' => 50, 'supplier_id' => $s1->id],
            ['code' => 'MP-MERKEN', 'name' => 'Merkén ahumado', 'category' => 'Condimento', 'type' => 'material', 'unit' => 'kg', 'unit_cost' => 8500, 'stock' => 0, 'transit' => 15, 'safety_stock' => 10, 'weekly_consumption' => 8, 'lead_time_days' => 7, 'target_weeks' => 6, 'min_purchase' => 10, 'purchase_multiple' => 5, 'supplier_id' => $s1->id],
            ['code' => 'MP-ALI-ROC', 'name' => 'Aliño rocoto', 'category' => 'Condimento', 'type' => 'material', 'unit' => 'kg', 'unit_cost' => 7200, 'stock' => 0, 'transit' => 15, 'safety_stock' => 10, 'weekly_consumption' => 6, 'lead_time_days' => 6, 'target_weeks' => 6, 'min_purchase' => 10, 'purchase_multiple' => 5, 'supplier_id' => $s1->id],
            ['code' => 'MP-ALI-LIM', 'name' => 'Aliño limón pimienta', 'category' => 'Condimento', 'type' => 'material', 'unit' => 'kg', 'unit_cost' => 6800, 'stock' => 0, 'transit' => 15, 'safety_stock' => 10, 'weekly_consumption' => 5, 'lead_time_days' => 6, 'target_weeks' => 6, 'min_purchase' => 10, 'purchase_multiple' => 5, 'supplier_id' => $s1->id],
            ['code' => 'SV-MAQUILA', 'name' => 'Maquila de producción', 'category' => 'Servicios', 'type' => 'service', 'unit' => 'servicio', 'unit_cost' => 3400, 'stock' => 0, 'transit' => 0, 'safety_stock' => 0, 'weekly_consumption' => 0, 'lead_time_days' => 0, 'target_weeks' => 0, 'min_purchase' => 0, 'purchase_multiple' => 1, 'supplier_id' => $s2->id],
            ['code' => 'EV-CAJA-150', 'name' => 'Caja kraft 150 g', 'category' => 'Envases', 'type' => 'material', 'unit' => 'unidad', 'unit_cost' => 120, 'stock' => 0, 'transit' => 5000, 'safety_stock' => 500, 'weekly_consumption' => 600, 'lead_time_days' => 7, 'target_weeks' => 4, 'min_purchase' => 500, 'purchase_multiple' => 500, 'supplier_id' => $s3->id],
            ['code' => 'EV-CAJA-045', 'name' => 'Caja kraft 45 g', 'category' => 'Envases', 'type' => 'material', 'unit' => 'unidad', 'unit_cost' => 85, 'stock' => 0, 'transit' => 3000, 'safety_stock' => 800, 'weekly_consumption' => 900, 'lead_time_days' => 7, 'target_weeks' => 4, 'min_purchase' => 1000, 'purchase_multiple' => 500, 'supplier_id' => $s3->id],
            ['code' => 'EV-BOLSA-150', 'name' => 'Bolsa sellada 150 g', 'category' => 'Envases', 'type' => 'material', 'unit' => 'unidad', 'unit_cost' => 45, 'stock' => 0, 'transit' => 2000, 'safety_stock' => 600, 'weekly_consumption' => 720, 'lead_time_days' => 5, 'target_weeks' => 4, 'min_purchase' => 1000, 'purchase_multiple' => 500, 'supplier_id' => $s3->id],
        ];
        foreach ($inputs as $data) {
            Input::query()->firstOrCreate(['code' => $data['code']], $data);
        }
    }

    private function seedProducts(): void
    {
        $products = [
            ['sku' => 'PAP-150-SAL', 'name' => 'Papas Chips Sal de Mar 150 g', 'grams' => 150, 'units_per_box' => 12, 'stock_boxes' => 0, 'min_stock_boxes' => 20, 'sale_price_box' => 3990, 'status' => 'active'],
            ['sku' => 'PAP-150-MER', 'name' => 'Papas Chips Merkén 150 g', 'grams' => 150, 'units_per_box' => 12, 'stock_boxes' => 0, 'min_stock_boxes' => 20, 'sale_price_box' => 4290, 'status' => 'active'],
            ['sku' => 'PAP-150-ROC', 'name' => 'Papas Chips Rocoto 150 g', 'grams' => 150, 'units_per_box' => 12, 'stock_boxes' => 0, 'min_stock_boxes' => 15, 'sale_price_box' => 4290, 'status' => 'active'],
            ['sku' => 'PAP-150-LIM', 'name' => 'Papas Chips Limón Pimienta 150 g', 'grams' => 150, 'units_per_box' => 12, 'stock_boxes' => 0, 'min_stock_boxes' => 15, 'sale_price_box' => 4190, 'status' => 'active'],
            ['sku' => 'PAP-045-SAL', 'name' => 'Papas Chips Sal de Mar 45 g', 'grams' => 45, 'units_per_box' => 42, 'stock_boxes' => 0, 'min_stock_boxes' => 15, 'sale_price_box' => 2490, 'status' => 'active'],
            ['sku' => 'PAP-045-MER', 'name' => 'Papas Chips Merkén 45 g', 'grams' => 45, 'units_per_box' => 42, 'stock_boxes' => 0, 'min_stock_boxes' => 15, 'sale_price_box' => 2690, 'status' => 'active'],
        ];
        foreach ($products as $data) {
            Product::query()->firstOrCreate(['sku' => $data['sku']], $data);
        }

        $inputs = collect(Input::query()->get())->keyBy('code');
        $recipes = [
            'PAP-150-SAL' => [['MP-PAPA-01', 1.4], ['MP-ACEITE', 0.15], ['MP-SAL', 0.025], ['SV-MAQUILA', 1], ['EV-CAJA-150', 1], ['EV-BOLSA-150', 1]],
            'PAP-150-MER' => [['MP-PAPA-01', 1.4], ['MP-ACEITE', 0.15], ['MP-SAL', 0.02], ['MP-MERKEN', 0.03], ['SV-MAQUILA', 1], ['EV-CAJA-150', 1], ['EV-BOLSA-150', 1]],
            'PAP-150-ROC' => [['MP-PAPA-01', 1.4], ['MP-ACEITE', 0.15], ['MP-SAL', 0.02], ['MP-ALI-ROC', 0.035], ['SV-MAQUILA', 1], ['EV-CAJA-150', 1], ['EV-BOLSA-150', 1]],
            'PAP-150-LIM' => [['MP-PAPA-02', 1.4], ['MP-ACEITE', 0.15], ['MP-SAL', 0.02], ['MP-ALI-LIM', 0.03], ['SV-MAQUILA', 1], ['EV-CAJA-150', 1], ['EV-BOLSA-150', 1]],
            'PAP-045-SAL' => [['MP-PAPA-01', 0.42], ['MP-ACEITE', 0.045], ['MP-SAL', 0.008], ['SV-MAQUILA', 1], ['EV-CAJA-045', 1]],
            'PAP-045-MER' => [['MP-PAPA-01', 0.42], ['MP-ACEITE', 0.045], ['MP-SAL', 0.006], ['MP-MERKEN', 0.01], ['SV-MAQUILA', 1], ['EV-CAJA-045', 1]],
        ];
        foreach ($recipes as $sku => $items) {
            $product = Product::query()->where('sku', $sku)->firstOrFail();
            foreach ($items as [$code, $qty]) {
                Recipe::query()->updateOrCreate(
                    ['product_id' => $product->id, 'input_id' => $inputs[$code]->id],
                    ['qty_per_box' => $qty]
                );
            }
        }
    }

    private function seedCustomers(): void
    {
        $customers = [
            ['code' => 'CLI-JUMBO', 'business_name' => 'Cencosud Retail S.A.', 'trade_name' => 'Jumbo', 'rut' => '76.345.678-9', 'type' => 'Retail', 'channel' => 'Retail', 'contact' => 'Francisca Torres', 'email' => 'compras@jumbo.cl', 'payment_terms' => '30 días'],
            ['code' => 'CLI-UNIMARC', 'business_name' => 'SMU S.A.', 'trade_name' => 'Unimarc', 'rut' => '76.456.789-0', 'type' => 'Retail', 'channel' => 'Retail', 'contact' => 'Roberto Díaz', 'email' => 'compras@unimarc.cl', 'payment_terms' => '30 días'],
            ['code' => 'CLI-LIDER', 'business_name' => 'Walmart Chile S.A.', 'trade_name' => 'Lider', 'rut' => '76.567.890-1', 'type' => 'Retail', 'channel' => 'Retail', 'contact' => 'Marcela Soto', 'email' => 'compras@lider.cl', 'payment_terms' => '45 días'],
            ['code' => 'CLI-GOURMET', 'business_name' => 'Distribuidora Gourmet SpA', 'trade_name' => 'Mercado Gourmet', 'rut' => '76.678.901-2', 'type' => 'Distribuidor', 'channel' => 'Food Service', 'contact' => 'Ignacio Reyes', 'email' => 'pedidos@mercadogourmet.cl', 'payment_terms' => '15 días'],
            ['code' => 'CLI-ONLINE', 'business_name' => 'E-Commerce PatPot Ltda', 'trade_name' => 'PatPot Online', 'rut' => '76.789.012-3', 'type' => 'Online', 'channel' => 'E-Commerce', 'contact' => 'Equipo TI', 'email' => 'ti@patpot.cl', 'payment_terms' => 'Contado'],
        ];
        foreach ($customers as $data) {
            Customer::query()->updateOrCreate(['code' => $data['code']], $data);
        }

        $stores = [
            ['customer_code' => 'CLI-JUMBO', 'code' => 'JUM-NU', 'name' => 'Jumbo Ñuñoa', 'city' => 'Santiago', 'region' => 'Metropolitana'],
            ['customer_code' => 'CLI-JUMBO', 'code' => 'JUM-LH', 'name' => 'Jumbo Las Heras', 'city' => 'Santiago', 'region' => 'Metropolitana'],
            ['customer_code' => 'CLI-UNIMARC', 'code' => 'UNI-PR', 'name' => 'Unimarc Providencia', 'city' => 'Santiago', 'region' => 'Metropolitana'],
            ['customer_code' => 'CLI-UNIMARC', 'code' => 'UNI-VI', 'name' => 'Unimarc Villa Maria', 'city' => 'Santiago', 'region' => 'Metropolitana'],
            ['customer_code' => 'CLI-LIDER', 'code' => 'LID-ES', 'name' => 'Lider Espacio Urbano', 'city' => 'Santiago', 'region' => 'Metropolitana'],
            ['customer_code' => 'CLI-LIDER', 'code' => 'LID-PC', 'name' => 'Lider Puente Alto', 'city' => 'Santiago', 'region' => 'Metropolitana'],
            ['customer_code' => 'CLI-GOURMET', 'code' => 'MG-VI', 'name' => 'Mercado Gourmet Vitacura', 'city' => 'Santiago', 'region' => 'Metropolitana'],
            ['customer_code' => 'CLI-GOURMET', 'code' => 'MG-PR', 'name' => 'Mercado Gourmet Bellavista', 'city' => 'Santiago', 'region' => 'Metropolitana'],
            ['customer_code' => 'CLI-ONLINE', 'code' => 'PAT-ON', 'name' => 'Bodega Online', 'city' => 'Santiago', 'region' => 'Metropolitana'],
        ];
        foreach ($stores as $data) {
            $customer = Customer::query()->where('code', $data['customer_code'])->firstOrFail();
            Store::query()->updateOrCreate(
                ['customer_id' => $customer->id, 'code' => $data['code']],
                ['name' => $data['name'], 'city' => $data['city'], 'region' => $data['region'], 'status' => true]
            );
        }

        $products = Product::query()->get();
        $customers = Customer::query()->get();
        $basePrices = ['CLI-JUMBO' => 1.0, 'CLI-UNIMARC' => 0.98, 'CLI-LIDER' => 0.92, 'CLI-GOURMET' => 1.05, 'CLI-ONLINE' => 0.95];
        foreach ($customers as $customer) {
            $mult = $basePrices[$customer->code] ?? 1.0;
            foreach ($products as $product) {
                $price = round($product->sale_price_box * $mult);
                Price::query()->updateOrCreate(
                    ['customer_id' => $customer->id, 'product_id' => $product->id],
                    ['price_box' => $price]
                );
            }
        }
    }

    private function seedPurchasesAndReceive(InventoryService $inventoryService): void
    {
        $supplier1 = Supplier::query()->where('rut', '76.111.222-3')->firstOrFail();
        $supplier2 = Supplier::query()->where('rut', '76.333.444-5')->firstOrFail();
        $supplier3 = Supplier::query()->where('rut', '76.555.666-7')->firstOrFail();
        $inputs = collect(Input::query()->get())->keyBy('code');

        $received = [
            ['number' => 'OC-2026-001', 'supplier_id' => $supplier1->id, 'ordered_on' => '2026-09-01', 'expected_on' => '2026-09-04',
                'lines' => [['code' => 'MP-PAPA-01', 'qty' => 3000, 'cost' => 1100], ['code' => 'MP-PAPA-02', 'qty' => 2000, 'cost' => 1200]],
            ],
            ['number' => 'OC-2026-002', 'supplier_id' => $supplier2->id, 'ordered_on' => '2026-09-03', 'expected_on' => '2026-09-08',
                'lines' => [['code' => 'MP-ACEITE', 'qty' => 400, 'cost' => 1800]],
            ],
            ['number' => 'OC-2026-003', 'supplier_id' => $supplier1->id, 'ordered_on' => '2026-09-08', 'expected_on' => '2026-09-11',
                'lines' => [['code' => 'MP-SAL', 'qty' => 100, 'cost' => 620], ['code' => 'MP-MERKEN', 'qty' => 15, 'cost' => 8500]],
            ],
            ['number' => 'OC-2026-004', 'supplier_id' => $supplier3->id, 'ordered_on' => '2026-09-10', 'expected_on' => '2026-09-17',
                'lines' => [['code' => 'EV-CAJA-150', 'qty' => 3000, 'cost' => 120], ['code' => 'EV-CAJA-045', 'qty' => 3000, 'cost' => 85], ['code' => 'EV-BOLSA-150', 'qty' => 2000, 'cost' => 45]],
            ],
            ['number' => 'OC-2026-004B', 'supplier_id' => $supplier1->id, 'ordered_on' => '2026-09-10', 'expected_on' => '2026-09-14',
                'lines' => [['code' => 'MP-ALI-ROC', 'qty' => 15, 'cost' => 7200], ['code' => 'MP-ALI-LIM', 'qty' => 15, 'cost' => 6800]],
            ],
            ['number' => 'OC-2026-005', 'supplier_id' => $supplier1->id, 'ordered_on' => '2026-09-15', 'expected_on' => '2026-09-18', 'receive' => false,
                'lines' => [['code' => 'MP-PAPA-01', 'qty' => 4000, 'cost' => 1100, 'rcvd' => 2500], ['code' => 'MP-PAPA-02', 'qty' => 2500, 'cost' => 1200, 'rcvd' => 0]],
            ],
            ['number' => 'OC-2026-006', 'supplier_id' => $supplier2->id, 'ordered_on' => '2026-09-18', 'expected_on' => '2026-09-23', 'receive' => false,
                'lines' => [['code' => 'MP-ACEITE', 'qty' => 500, 'cost' => 1800, 'rcvd' => 0]],
            ],
        ];

        foreach ($received as $pData) {
            $purchase = Purchase::query()->firstOrCreate(
                ['number' => $pData['number']],
                ['supplier_id' => $pData['supplier_id'], 'ordered_on' => $pData['ordered_on'], 'expected_on' => $pData['expected_on'], 'status' => 'pending']
            );

            foreach ($pData['lines'] as $lineData) {
                $purchase->lines()->firstOrCreate(
                    ['input_id' => $inputs[$lineData['code']]->id],
                    ['ordered_quantity' => $lineData['qty'], 'unit_cost' => $lineData['cost']]
                );
            }

            if (($pData['receive'] ?? true) !== false) {
                $qtyMap = [];
                foreach ($pData['lines'] as $lineData) {
                    $line = $purchase->lines->where('input_id', $inputs[$lineData['code']]->id)->first();
                    if ($line) {
                        $qtyMap[$line->id] = $lineData['qty'];
                    }
                }
                if (!empty($qtyMap)) {
                    $inventoryService->receivePurchase($purchase, $qtyMap, $pData['ordered_on']);
                }
            } else {
                foreach ($pData['lines'] as $lineData) {
                    $rcvd = $lineData['rcvd'] ?? 0;
                    if ($rcvd > 0) {
                        $line = $purchase->lines->where('input_id', $inputs[$lineData['code']]->id)->first();
                        if ($line) {
                            $inventoryService->receivePurchase($purchase, [$line->id => $rcvd], $pData['ordered_on']);
                        }
                    }
                }
                $allReceived = $purchase->fresh()->lines->every(fn ($l) => (float) $l->received_quantity >= (float) $l->ordered_quantity);
                $anyReceived = $purchase->fresh()->lines->some(fn ($l) => (float) $l->received_quantity > 0);
                $purchase->update(['status' => $allReceived ? 'received' : ($anyReceived ? 'partial' : 'pending')]);
            }
        }
    }

    private function seedProductions(InventoryService $inventoryService): void
    {
        $products = Product::query()->get()->keyBy('sku');
        $productions = [
            ['number' => 'OP-2026-001', 'sku' => 'PAP-150-SAL', 'planned' => 200, 'actual' => 200, 'planned_on' => '2026-09-05', 'completed_on' => '2026-09-06'],
            ['number' => 'OP-2026-002', 'sku' => 'PAP-150-MER', 'planned' => 150, 'actual' => 150, 'planned_on' => '2026-09-07', 'completed_on' => '2026-09-08'],
            ['number' => 'OP-2026-003', 'sku' => 'PAP-045-SAL', 'planned' => 180, 'actual' => 180, 'planned_on' => '2026-09-10', 'completed_on' => '2026-09-11'],
            ['number' => 'OP-2026-004', 'sku' => 'PAP-150-ROC', 'planned' => 120, 'actual' => 120, 'planned_on' => '2026-09-13', 'completed_on' => '2026-09-14'],
            ['number' => 'OP-2026-005', 'sku' => 'PAP-150-LIM', 'planned' => 100, 'actual' => 100, 'planned_on' => '2026-09-14', 'completed_on' => '2026-09-15'],
            ['number' => 'OP-2026-006', 'sku' => 'PAP-045-MER', 'planned' => 150, 'actual' => 150, 'planned_on' => '2026-09-17', 'completed_on' => '2026-09-18'],
        ];
        foreach ($productions as $pData) {
            $product = $products[$pData['sku']];
            $production = Production::query()->firstOrCreate(
                ['number' => $pData['number']],
                ['product_id' => $product->id, 'planned_boxes' => $pData['planned'], 'planned_on' => $pData['planned_on'], 'notes' => 'Producción programada']
            );
            if ($production->fresh()->status !== 'closed') {
                $inventoryService->closeProduction($production, $pData['actual'], $pData['completed_on']);
            }
        }
    }

    private function seedOrders(InventoryService $inventoryService): void
    {
        $allProducts = Product::query()->get()->keyBy('sku');
        $orders = [
            [
                'number' => 'PED-2026-001', 'customer_code' => 'CLI-JUMBO', 'store_code' => 'JUM-NU',
                'ordered_on' => '2026-09-06', 'delivery_on' => '2026-09-09',
                'lines' => [['sku' => 'PAP-150-SAL', 'boxes' => 50, 'dispatch' => 50], ['sku' => 'PAP-150-MER', 'boxes' => 30, 'dispatch' => 30]],
            ],
            [
                'number' => 'PED-2026-002', 'customer_code' => 'CLI-UNIMARC', 'store_code' => 'UNI-PR',
                'ordered_on' => '2026-09-08', 'delivery_on' => '2026-09-11',
                'lines' => [['sku' => 'PAP-150-SAL', 'boxes' => 40, 'dispatch' => 40], ['sku' => 'PAP-045-SAL', 'boxes' => 25, 'dispatch' => 25]],
            ],
            [
                'number' => 'PED-2026-003', 'customer_code' => 'CLI-LIDER', 'store_code' => 'LID-ES',
                'ordered_on' => '2026-09-11', 'delivery_on' => '2026-09-14',
                'lines' => [['sku' => 'PAP-150-SAL', 'boxes' => 60, 'dispatch' => 60], ['sku' => 'PAP-150-MER', 'boxes' => 40, 'dispatch' => 40], ['sku' => 'PAP-045-SAL', 'boxes' => 30, 'dispatch' => 30]],
            ],
            [
                'number' => 'PED-2026-004', 'customer_code' => 'CLI-GOURMET', 'store_code' => 'MG-VI',
                'ordered_on' => '2026-09-14', 'delivery_on' => '2026-09-17',
                'lines' => [['sku' => 'PAP-150-MER', 'boxes' => 25, 'dispatch' => 25], ['sku' => 'PAP-150-ROC', 'boxes' => 20, 'dispatch' => 20], ['sku' => 'PAP-045-MER', 'boxes' => 15, 'dispatch' => 15]],
            ],
            [
                'number' => 'PED-2026-005', 'customer_code' => 'CLI-JUMBO', 'store_code' => 'JUM-LH',
                'ordered_on' => '2026-09-16', 'delivery_on' => '2026-09-19',
                'lines' => [['sku' => 'PAP-150-SAL', 'boxes' => 40, 'dispatch' => 40], ['sku' => 'PAP-150-MER', 'boxes' => 20, 'dispatch' => 20]],
            ],
            [
                'number' => 'PED-2026-006', 'customer_code' => 'CLI-UNIMARC', 'store_code' => 'UNI-VI',
                'ordered_on' => '2026-09-18', 'delivery_on' => '2026-09-21',
                'lines' => [['sku' => 'PAP-150-SAL', 'boxes' => 30, 'dispatch' => 0], ['sku' => 'PAP-150-MER', 'boxes' => 40, 'dispatch' => 0]],
            ],
            [
                'number' => 'PED-2026-007', 'customer_code' => 'CLI-ONLINE', 'store_code' => 'PAT-ON',
                'ordered_on' => '2026-09-19', 'delivery_on' => '2026-09-22',
                'lines' => [['sku' => 'PAP-150-SAL', 'boxes' => 30, 'dispatch' => 0], ['sku' => 'PAP-045-SAL', 'boxes' => 40, 'dispatch' => 0]],
            ],
            [
                'number' => 'PED-2026-008', 'customer_code' => 'CLI-LIDER', 'store_code' => 'LID-PC',
                'ordered_on' => '2026-09-20', 'delivery_on' => '2026-09-23',
                'lines' => [['sku' => 'PAP-150-ROC', 'boxes' => 35, 'dispatch' => 0], ['sku' => 'PAP-150-LIM', 'boxes' => 30, 'dispatch' => 0]],
            ],
        ];

        foreach ($orders as $oData) {
            $customer = Customer::query()->where('code', $oData['customer_code'])->firstOrFail();
            $store = Store::query()->where('code', $oData['store_code'])->firstOrFail();
            $priceList = Price::query()->where('customer_id', $customer->id)->get()->keyBy('product_id');

            $order = Order::query()->firstOrCreate(
                ['number' => $oData['number']],
                ['customer_id' => $customer->id, 'store_id' => $store->id, 'ordered_on' => $oData['ordered_on'], 'delivery_on' => $oData['delivery_on'], 'notes' => '', 'status' => 'pending']
            );

            $quantities = [];
            foreach ($oData['lines'] as $lineData) {
                $product = $allProducts[$lineData['sku']];
                $price = $priceList->get($product->id)?->price_box ?? $product->sale_price_box;
                $line = $order->lines()->firstOrCreate(
                    ['product_id' => $product->id],
                    ['boxes' => $lineData['boxes'], 'price_box' => $price]
                );
                if (($lineData['dispatch'] ?? 0) > 0 && (int) $line->fresh()->dispatched_boxes < $lineData['dispatch']) {
                    $toDispatch = $lineData['dispatch'] - (int) $line->fresh()->dispatched_boxes;
                    $quantities[$line->id] = $toDispatch;
                }
            }

            if (!empty($quantities)) {
                $inventoryService->dispatchOrder($order, $quantities, $oData['delivery_on']);
            }
        }
    }

    private function seedRetail(): void
    {
        $products = Product::query()->get()->keyBy('sku');
        $stores = Store::query()->get()->keyBy('code');
        $retailData = [
            ['store_code' => 'JUM-NU', 'product_sku' => 'PAP-150-MER', 'stock_units' => 24, 'transit_units' => 0, 'weekly_sales' => 18, 'min_stock' => 12, 'reorder_point' => 24],
            ['store_code' => 'UNI-PR', 'product_sku' => 'PAP-150-SAL', 'stock_units' => 36, 'transit_units' => 0, 'weekly_sales' => 28, 'min_stock' => 12, 'reorder_point' => 24],
            ['store_code' => 'UNI-PR', 'product_sku' => 'PAP-045-SAL', 'stock_units' => 84, 'transit_units' => 0, 'weekly_sales' => 55, 'min_stock' => 42, 'reorder_point' => 84],
            ['store_code' => 'LID-ES', 'product_sku' => 'PAP-150-SAL', 'stock_units' => 12, 'transit_units' => 24, 'weekly_sales' => 42, 'min_stock' => 12, 'reorder_point' => 36],
            ['store_code' => 'LID-ES', 'product_sku' => 'PAP-150-MER', 'stock_units' => 6, 'transit_units' => 0, 'weekly_sales' => 25, 'min_stock' => 12, 'reorder_point' => 24],
            ['store_code' => 'MG-VI', 'product_sku' => 'PAP-150-MER', 'stock_units' => 18, 'transit_units' => 0, 'weekly_sales' => 15, 'min_stock' => 12, 'reorder_point' => 24],
            ['store_code' => 'MG-VI', 'product_sku' => 'PAP-150-ROC', 'stock_units' => 0, 'transit_units' => 12, 'weekly_sales' => 12, 'min_stock' => 12, 'reorder_point' => 24],
            ['store_code' => 'JUM-LH', 'product_sku' => 'PAP-150-SAL', 'stock_units' => 30, 'transit_units' => 0, 'weekly_sales' => 32, 'min_stock' => 12, 'reorder_point' => 24],
            ['store_code' => 'JUM-LH', 'product_sku' => 'PAP-045-SAL', 'stock_units' => 42, 'transit_units' => 0, 'weekly_sales' => 38, 'min_stock' => 42, 'reorder_point' => 84],
        ];
        foreach ($retailData as $data) {
            $store = $stores[$data['store_code']] ?? null;
            $product = $products[$data['product_sku']] ?? null;
            if ($store && $product) {
                \App\Models\Retail::query()->updateOrCreate(
                    ['store_id' => $store->id, 'product_id' => $product->id],
                    ['cataloged' => true, 'stock_units' => $data['stock_units'], 'transit_units' => $data['transit_units'], 'weekly_sales' => $data['weekly_sales'], 'min_stock' => $data['min_stock'], 'reorder_point' => $data['reorder_point']]
                );
            }
        }
    }

    private function seedTasks(): void
    {
        $tasks = [
            ['title' => 'Confirmar despacho PED-2026-005', 'owner' => 'María López', 'due_on' => '2026-09-19', 'priority' => 'high', 'module' => 'Ventas', 'status' => 'pending', 'notes' => 'Despacho parcial a Jumbo Las Heras, falta envases y 045 sal.'],
            ['title' => 'Seguimiento recepción parcial OC-2026-005', 'owner' => 'Carlos Muñoz', 'due_on' => '2026-09-18', 'priority' => 'high', 'module' => 'Compras', 'status' => 'pending', 'notes' => 'Falta recepción de 1500 kg papa shiny pink y 2500 kg papa crits.'],
            ['title' => 'Aprobar orden OC-2026-006', 'owner' => 'Administrador', 'due_on' => '2026-09-20', 'priority' => 'medium', 'module' => 'Compras', 'status' => 'pending', 'notes' => 'Compra de aceite para reponer stock.'],
            ['title' => 'Revisar cobertura retail Lider', 'owner' => 'Administrador', 'due_on' => '2026-09-21', 'priority' => 'medium', 'module' => 'Retail', 'status' => 'pending', 'notes' => 'Lider Espacio Urbano con bajo stock de Merkén.'],
            ['title' => 'Programar producción Merkén 45g', 'owner' => 'María López', 'due_on' => '2026-09-22', 'priority' => 'low', 'module' => 'Producción', 'status' => 'pending', 'notes' => 'Merken 45 g para reposición online.'],
            ['title' => 'Contestar consulta Jumbo sobre stock', 'owner' => 'María López', 'due_on' => '2026-09-17', 'priority' => 'medium', 'module' => 'Ventas', 'status' => 'completed', 'completed_on' => '2026-09-17', 'notes' => 'Jumbo preguntó por disponibilidad Sal 150 g.'],
            ['title' => 'Cerrar recepción OC-2026-004', 'owner' => 'Carlos Muñoz', 'due_on' => '2026-09-17', 'priority' => 'high', 'module' => 'Compras', 'status' => 'completed', 'completed_on' => '2026-09-17', 'notes' => 'Envases recibidos correctamente.'],
            ['title' => 'Revisar precios para Mercado Gourmet', 'owner' => 'Administrador', 'due_on' => '2026-09-23', 'priority' => 'low', 'module' => 'Ventas', 'status' => 'pending', 'notes' => 'Actualizar precio especial para línea premium.'],
            ['title' => 'Auditar movimientos de inventario', 'owner' => 'Administrador', 'due_on' => '2026-09-25', 'priority' => 'low', 'module' => 'Inventario', 'status' => 'pending', 'notes' => 'Revisión mensual de trazabilidad.'],
        ];
        foreach ($tasks as $data) {
            Task::query()->create($data);
        }
    }
}
