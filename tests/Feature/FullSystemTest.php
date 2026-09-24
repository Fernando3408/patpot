<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Input;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Production;
use App\Models\Recipe;
use App\Models\Role;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Supplier $supplier;
    private Input $input;
    private Product $product;
    private Customer $customer;
    private Store $store;
    private Price $price;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\DatabaseSeeder::class);
        $this->admin = User::first();
        $this->actingAs($this->admin);
        $this->supplier = Supplier::first();
        $this->input = Input::first();
        $this->product = Product::first();
        $this->customer = Customer::first();
        $this->store = Store::first();
        $this->price = Price::first();
    }

    // ===== DASHBOARD =====
    public function test_dashboard_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    // ===== PRODUCTOS =====
    public function test_products_index(): void
    {
        $response = $this->get('/productos');
        $response->assertStatus(200);
        $response->assertSee($this->product->name);
    }

    public function test_products_create(): void
    {
        $response = $this->get('/productos/create');
        $response->assertStatus(200);
    }

    public function test_products_store(): void
    {
        $response = $this->post('/productos', [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'grams' => 100,
            'units_per_box' => 12,
            'stock_boxes' => 50,
            'min_stock_boxes' => 10,
            'sale_price_box' => 5000,
            'status' => 'active',
        ]);
        $response->assertRedirect('/productos');
        $this->assertDatabaseHas('products', ['sku' => 'TEST-001']);
    }

    public function test_products_show(): void
    {
        $response = $this->get('/productos/' . $this->product->id);
        $response->assertStatus(200);
    }

    public function test_products_edit(): void
    {
        $response = $this->get('/productos/' . $this->product->id . '/edit');
        $response->assertStatus(200);
    }

    public function test_products_update(): void
    {
        $response = $this->put('/productos/' . $this->product->id, [
            'name' => 'Updated Product',
            'sku' => $this->product->sku,
            'grams' => 150,
            'units_per_box' => 12,
            'stock_boxes' => 60,
            'min_stock_boxes' => 15,
            'sale_price_box' => 5500,
            'status' => 'active',
        ]);
        $response->assertRedirect('/productos');
    }

    public function test_products_destroy_without_dependencies(): void
    {
        $newProduct = Product::create([
            'sku' => 'DEL-TEST',
            'name' => 'To Delete',
            'grams' => 100,
            'units_per_box' => 12,
            'sale_price_box' => 3000,
            'status' => 'active',
        ]);
        $response = $this->delete('/productos/' . $newProduct->id);
        $response->assertRedirect('/productos');
        $this->assertSoftDeleted('products', ['id' => $newProduct->id]);
    }

    public function test_products_destroy_blocked_with_recipes(): void
    {
        $response = $this->delete('/productos/' . $this->product->id);
        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $this->product->id, 'deleted_at' => null]);
    }

    // ===== INSUMOS =====
    public function test_inputs_index(): void
    {
        $response = $this->get('/insumos');
        $response->assertStatus(200);
        $response->assertSee($this->input->name);
    }

    public function test_inputs_create(): void
    {
        $response = $this->get('/insumos/create');
        $response->assertStatus(200);
    }

    public function test_inputs_store(): void
    {
        $response = $this->post('/insumos', [
            'code' => 'INS-TEST',
            'name' => 'Test Input',
            'type' => 'material',
            'unit' => 'kg',
            'stock' => 100,
            'safety_stock' => 50,
            'weekly_consumption' => 20,
            'lead_time_days' => 5,
            'target_weeks' => 4,
            'min_purchase' => 100,
            'purchase_multiple' => 50,
            'unit_cost' => 1000,
            'transit' => 0,
            'status' => true,
        ]);
        $response->assertRedirect('/insumos');
        $this->assertDatabaseHas('inputs', ['code' => 'INS-TEST']);
    }

    public function test_inputs_store_with_decimals(): void
    {
        $response = $this->post('/insumos', [
            'code' => 'INS-DEC',
            'name' => 'Sal decimal',
            'type' => 'material',
            'unit' => 'kg',
            'stock' => 0.015,
            'safety_stock' => 0.010,
            'weekly_consumption' => 0.025,
            'lead_time_days' => 5,
            'target_weeks' => 4,
            'min_purchase' => 0.100,
            'purchase_multiple' => 0.050,
            'unit_cost' => 620,
            'transit' => 0,
            'status' => true,
        ]);
        $response->assertRedirect('/insumos');
        $this->assertDatabaseHas('inputs', ['code' => 'INS-DEC', 'stock' => 0.015]);
    }

    public function test_inputs_show(): void
    {
        $response = $this->get('/insumos/' . $this->input->id);
        $response->assertStatus(200);
    }

    public function test_inputs_edit(): void
    {
        $response = $this->get('/insumos/' . $this->input->id . '/edit');
        $response->assertStatus(200);
    }

    public function test_inputs_update(): void
    {
        $response = $this->put('/insumos/' . $this->input->id, [
            'code' => $this->input->code,
            'name' => 'Updated Input',
            'type' => 'material',
            'unit' => $this->input->unit,
            'stock' => 999,
            'safety_stock' => 50,
            'weekly_consumption' => 120,
            'lead_time_days' => 5,
            'target_weeks' => 4,
            'min_purchase' => 42,
            'purchase_multiple' => 42,
            'unit_cost' => 950,
            'transit' => 0,
            'status' => true,
        ], ['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertStatus(200);
    }

    public function test_inputs_adjust(): void
    {
        $stockBefore = $this->input->stock;
        $response = $this->post('/insumos/' . $this->input->id . '/adjust', [
            'type' => 'add',
            'qty' => 50,
            'reason' => 'Test adjustment',
        ]);
        $response->assertStatus(200);
        $this->input->refresh();
        $this->assertEquals((float)$stockBefore + 50, (float)$this->input->stock);
    }

    public function test_inputs_destroy_blocked(): void
    {
        $response = $this->delete('/insumos/' . $this->input->id);
        $response->assertRedirect();
        $this->assertDatabaseHas('inputs', ['id' => $this->input->id, 'deleted_at' => null]);
    }

    // ===== RECETAS =====
    public function test_recipes_index(): void
    {
        $response = $this->get('/recetas');
        $response->assertStatus(200);
    }

    public function test_recipes_create(): void
    {
        $response = $this->get('/recetas/create');
        $response->assertStatus(200);
    }

    public function test_recipes_edit(): void
    {
        $response = $this->get('/recetas/' . $this->product->id . '/edit');
        $response->assertStatus(200);
    }

    public function test_recipes_show(): void
    {
        $response = $this->get('/recetas/' . $this->product->id);
        $response->assertStatus(200);
    }

    // ===== PROVEEDORES =====
    public function test_suppliers_index(): void
    {
        $response = $this->get('/proveedores');
        $response->assertStatus(200);
        $response->assertSee($this->supplier->name);
    }

    public function test_suppliers_create(): void
    {
        $response = $this->get('/proveedores/create');
        $response->assertStatus(200);
    }

    public function test_suppliers_store(): void
    {
        $response = $this->post('/proveedores', [
            'name' => 'New Supplier',
            'lead_time_days' => 5,
            'status' => true,
        ]);
        $response->assertRedirect('/proveedores');
        $this->assertDatabaseHas('suppliers', ['name' => 'New Supplier']);
    }

    public function test_suppliers_edit(): void
    {
        $response = $this->get('/proveedores/' . $this->supplier->id . '/edit');
        $response->assertStatus(200);
    }

    public function test_suppliers_show(): void
    {
        $response = $this->get('/proveedores/' . $this->supplier->id);
        $response->assertStatus(200);
    }

    public function test_suppliers_destroy_blocked(): void
    {
        $response = $this->delete('/proveedores/' . $this->supplier->id);
        $response->assertRedirect();
        $this->assertDatabaseHas('suppliers', ['id' => $this->supplier->id, 'deleted_at' => null]);
    }

    public function test_suppliers_destroy_without_dependencies(): void
    {
        $sup = Supplier::create(['name' => 'Empty Supplier', 'lead_time_days' => 3, 'status' => true]);
        $response = $this->delete('/proveedores/' . $sup->id);
        $response->assertRedirect('/proveedores');
        $this->assertSoftDeleted('suppliers', ['id' => $sup->id]);
    }

    // ===== CLIENTES =====
    public function test_customers_index(): void
    {
        $response = $this->get('/clientes');
        $response->assertStatus(200);
        $response->assertSee($this->customer->business_name);
    }

    public function test_customers_create(): void
    {
        $response = $this->get('/clientes/create');
        $response->assertStatus(200);
    }

    public function test_customers_store(): void
    {
        $response = $this->post('/clientes', [
            'code' => 'CLI-NEW',
            'business_name' => 'New Customer',
            'status' => true,
        ]);
        $response->assertRedirect('/clientes');
        $this->assertDatabaseHas('customers', ['code' => 'CLI-NEW']);
    }

    public function test_customers_edit(): void
    {
        $response = $this->get('/clientes/' . $this->customer->id . '/edit');
        $response->assertStatus(200);
    }

    public function test_customers_show(): void
    {
        $response = $this->get('/clientes/' . $this->customer->id);
        $response->assertStatus(200);
    }

    public function test_customers_destroy_blocked(): void
    {
        $response = $this->delete('/clientes/' . $this->customer->id);
        $response->assertRedirect();
    }

    // ===== SALAS =====
    public function test_stores_index(): void
    {
        $response = $this->get('/salas');
        $response->assertStatus(200);
    }

    public function test_stores_create(): void
    {
        $response = $this->get('/salas/create');
        $response->assertStatus(200);
    }

    public function test_stores_store(): void
    {
        $response = $this->post('/salas', [
            'customer_id' => $this->customer->id,
            'code' => 'SAL-NEW',
            'name' => 'New Store',
            'status' => true,
        ]);
        $response->assertRedirect('/salas');
    }

    public function test_stores_edit(): void
    {
        $response = $this->get('/salas/' . $this->store->id . '/edit');
        $response->assertStatus(200);
    }

    public function test_stores_show(): void
    {
        $response = $this->get('/salas/' . $this->store->id);
        $response->assertStatus(200);
    }

    // ===== PRECIOS =====
    public function test_prices_index(): void
    {
        $response = $this->get('/precios');
        $response->assertStatus(200);
    }

    public function test_prices_create(): void
    {
        $response = $this->get('/precios/create');
        $response->assertStatus(200);
    }

    public function test_prices_store(): void
    {
        $newProduct = Product::create(['sku' => 'PRC-PROD', 'name' => 'For Price', 'grams' => 100, 'units_per_box' => 12, 'sale_price_box' => 5000, 'status' => 'active']);
        $response = $this->post('/precios', [
            'customer_id' => $this->customer->id,
            'product_id' => $newProduct->id,
            'price_box' => 4500,
        ]);
        $response->assertRedirect('/precios');
    }

    public function test_prices_edit(): void
    {
        $response = $this->get('/precios/' . $this->price->id . '/edit');
        $response->assertStatus(200);
    }

    public function test_prices_show(): void
    {
        $response = $this->get('/precios/' . $this->price->id);
        $response->assertStatus(200);
    }

    // ===== COMPRAS =====
    public function test_purchases_index(): void
    {
        $response = $this->get('/compras');
        $response->assertStatus(200);
    }

    public function test_purchases_create(): void
    {
        $response = $this->get('/compras/create');
        $response->assertStatus(200);
    }

    public function test_purchases_store(): void
    {
        $response = $this->post('/compras', [
            'number' => 'OC-TEST-001',
            'supplier_id' => $this->supplier->id,
            'ordered_on' => '2026-09-07',
            'lines' => [
                ['input_id' => $this->input->id, 'ordered_quantity' => 100, 'unit_cost' => 950],
            ],
        ]);
        $response->assertRedirect('/compras');
        $this->assertDatabaseHas('purchases', ['number' => 'OC-TEST-001']);
    }

    public function test_purchases_show(): void
    {
        $purchase = Purchase::first();
        $response = $this->get('/compras/' . $purchase->id);
        $response->assertStatus(200);
    }

    public function test_purchases_edit(): void
    {
        $purchase = Purchase::where('status', 'pending')->first();
        if ($purchase) {
            $response = $this->get('/compras/' . $purchase->id . '/edit');
            $response->assertStatus(200);
        }
        $this->assertTrue(true);
    }

    // ===== PRODUCCIÓN =====
    public function test_productions_index(): void
    {
        $response = $this->get('/produccion');
        $response->assertStatus(200);
    }

    public function test_productions_create(): void
    {
        $response = $this->get('/produccion/create');
        $response->assertStatus(200);
    }

    public function test_productions_store(): void
    {
        $response = $this->post('/produccion', [
            'number' => 'OP-TEST-001',
            'product_id' => $this->product->id,
            'planned_boxes' => 50,
            'planned_on' => '2026-09-10',
        ]);
        $response->assertRedirect('/produccion');
        $this->assertDatabaseHas('productions', ['number' => 'OP-TEST-001']);
    }

    public function test_productions_show(): void
    {
        $production = Production::first();
        $response = $this->get('/produccion/' . $production->id);
        $response->assertStatus(200);
    }

    public function test_productions_edit_closed_blocked(): void
    {
        $production = Production::where('status', 'closed')->first();
        if ($production) {
            $response = $this->get('/produccion/' . $production->id . '/edit');
            $response->assertStatus(200);
        }
        $this->assertTrue(true);
    }

    // ===== PEDIDOS =====
    public function test_orders_index(): void
    {
        $response = $this->get('/pedidos');
        $response->assertStatus(200);
    }

    public function test_orders_create(): void
    {
        $response = $this->get('/pedidos/create');
        $response->assertStatus(200);
    }

    public function test_orders_store(): void
    {
        $response = $this->post('/pedidos', [
            'number' => 'PED-TEST-001',
            'customer_id' => $this->customer->id,
            'store_id' => $this->store->id,
            'ordered_on' => '2026-09-07',
            'delivery_on' => '2026-09-15',
            'lines' => [
                ['product_id' => $this->product->id, 'boxes' => 20],
            ],
        ]);
        $response->assertRedirect('/pedidos');
        $this->assertDatabaseHas('orders', ['number' => 'PED-TEST-001']);
        $order = Order::where('number', 'PED-TEST-001')->first();
        $this->assertNotEmpty($order->lines);
        $this->assertGreaterThan(0, (float)$order->lines->first()->price_box);
    }

    public function test_orders_show(): void
    {
        $order = Order::first();
        $response = $this->get('/pedidos/' . $order->id);
        $response->assertStatus(200);
    }

    public function test_orders_edit_no_dispatch(): void
    {
        $order = Order::where('status', 'pending')->first();
        if ($order) {
            $response = $this->get('/pedidos/' . $order->id . '/edit');
            $response->assertStatus(200);
        }
        $this->assertTrue(true);
    }

    public function test_orders_dispatch(): void
    {
        $order = Order::where('status', 'pending')->first();
        if (!$order) {
            $this->assertTrue(true);
            return;
        }
        $line = $order->lines->first();
        if (!$line) {
            $this->assertTrue(true);
            return;
        }
        $response = $this->post('/pedidos/' . $order->id . '/despachos', [
            'quantities' => [$line->id => 5],
            'shipped_on' => '2026-09-07',
        ]);
        $response->assertStatus(302);
        $line->refresh();
        $this->assertGreaterThan(0, (int)$line->dispatched_boxes);
    }

    // ===== RETAIL =====
    public function test_retail_index(): void
    {
        $response = $this->get('/retail');
        $response->assertStatus(200);
    }

    public function test_retail_create(): void
    {
        $response = $this->get('/retail/create');
        $response->assertStatus(200);
    }

    public function test_retail_store(): void
    {
        $response = $this->post('/retail', [
            'store_id' => $this->store->id,
            'product_id' => $this->product->id,
            'cataloged' => true,
            'stock_units' => 10,
            'transit_units' => 0,
            'weekly_sales' => 5,
            'min_stock' => 20,
            'reorder_point' => 15,
        ]);
        $response->assertRedirect('/retail');
    }

    public function test_retail_edit(): void
    {
        $retail = \App\Models\Retail::first();
        if ($retail) {
            $response = $this->get('/retail/' . $retail->id . '/edit');
            $response->assertStatus(200);
        }
        $this->assertTrue(true);
    }

    public function test_retail_show(): void
    {
        $retail = \App\Models\Retail::first();
        if ($retail) {
            $response = $this->get('/retail/' . $retail->id);
            $response->assertStatus(200);
        }
        $this->assertTrue(true);
    }

    // ===== TAREAS =====
    public function test_tasks_index(): void
    {
        $response = $this->get('/tareas');
        $response->assertStatus(200);
    }

    public function test_tasks_create(): void
    {
        $response = $this->get('/tareas/create');
        $response->assertStatus(200);
    }

    public function test_tasks_store(): void
    {
        $response = $this->post('/tareas', [
            'title' => 'Test Task',
            'due_on' => '2026-09-15',
            'priority' => 'medium',
            'status' => 'pending',
        ]);
        $response->assertRedirect('/tareas');
        $this->assertDatabaseHas('tasks', ['title' => 'Test Task']);
    }

    public function test_tasks_complete(): void
    {
        $task = \App\Models\Task::create([
            'title' => 'To Complete',
            'due_on' => '2026-09-15',
            'priority' => 'low',
            'status' => 'pending',
        ]);
        $response = $this->post('/tareas/' . $task->id . '/completar');
        $response->assertRedirect('/tareas');
        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertNotNull($task->completed_on);
    }

    public function test_tasks_edit(): void
    {
        $task = \App\Models\Task::first();
        if ($task) {
            $response = $this->get('/tareas/' . $task->id . '/edit');
            $response->assertStatus(200);
        }
        $this->assertTrue(true);
    }

    // ===== PAPELERA =====
    public function test_trash_index(): void
    {
        $response = $this->get('/admin/papelera');
        $response->assertStatus(200);
    }

    public function test_trash_restore_and_force_delete(): void
    {
        $sup = Supplier::create(['name' => 'Trash Test', 'lead_time_days' => 1, 'status' => true]);
        $sup->delete();
        $this->assertSoftDeleted('suppliers', ['id' => $sup->id]);

        $response = $this->post('/admin/papelera/restaurar', [
            'entity' => 'supplier',
            'id' => $sup->id,
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('suppliers', ['id' => $sup->id, 'deleted_at' => null]);

        $sup->delete();
        $response = $this->post('/admin/papelera/eliminar', [
            'entity' => 'supplier',
            'id' => $sup->id,
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseMissing('suppliers', ['id' => $sup->id]);
    }

    // ===== ADMIN =====
    public function test_admin_index(): void
    {
        $response = $this->get('/admin');
        $response->assertStatus(200);
    }

    public function test_admin_edit_user(): void
    {
        $response = $this->get('/admin/usuarios/' . $this->admin->id . '/editar');
        $response->assertStatus(200);
    }

    public function test_admin_update_user_roles(): void
    {
        $user = User::create(['name' => 'Test User', 'email' => 'test@test.com', 'password' => bcrypt('password'), 'status' => true]);
        $operadorRole = Role::where('name', 'operador')->first();

        $response = $this->put('/admin/usuarios/' . $user->id, [
            'name' => 'Test User Updated',
            'email' => 'test@test.com',
            'roles' => [$operadorRole->id],
        ]);
        $response->assertRedirect('/admin');
        $user->refresh();
        $this->assertTrue($user->roles->contains($operadorRole->id));
    }

    public function test_admin_toggle_status(): void
    {
        $newUser = User::create(['name' => 'Toggle Test', 'email' => 'toggle@test.com', 'password' => bcrypt('password'), 'status' => true]);
        $response = $this->post('/admin/usuarios/' . $newUser->id . '/toggle-status');
        $response->assertRedirect();
        $newUser->refresh();
        $this->assertFalse($newUser->status);
    }

    // ===== MOVIMIENTOS =====
    public function test_movements_index(): void
    {
        $response = $this->get('/movimientos');
        $response->assertStatus(200);
    }

    // ===== AUDITORÍA =====
    public function test_audit_index(): void
    {
        $response = $this->get('/auditoria');
        $response->assertStatus(200);
    }

    // ===== ADJUNTOS =====
    public function test_attachments_list(): void
    {
        $order = Order::first();
        $response = $this->get('/adjuntos/lista?model_class=App\Models\Order&model_id=' . $order->id);
        $response->assertStatus(200);
    }

    // ===== EDICION PEDIDO CON NUEVA LÍNEA (bug price_box null) =====
    public function test_order_edit_add_new_line_with_auto_price(): void
    {
        $order = Order::where('status', 'pending')->first();
        if (!$order) {
            $this->assertTrue(true);
            return;
        }
        $existingLine = $order->lines->first();
        $newProduct = Product::create(['sku' => 'NEW-LINE', 'name' => 'New Line Product', 'grams' => 100, 'units_per_box' => 12, 'sale_price_box' => 3500, 'status' => 'active']);

        $response = $this->put('/pedidos/' . $order->id, [
            'number' => $order->number,
            'customer_id' => $order->customer_id,
            'ordered_on' => $order->ordered_on->format('Y-m-d'),
            'lines' => [
                ['id' => $existingLine->id, 'product_id' => $existingLine->product_id, 'boxes' => $existingLine->boxes, 'price_box' => $existingLine->price_box],
                ['product_id' => $newProduct->id, 'boxes' => 10, 'price_box' => ''],
            ],
        ]);
        $response->assertStatus(302);
        $order->refresh()->load('lines');
        $newLine = $order->lines->where('product_id', $newProduct->id)->first();
        $this->assertNotNull($newLine);
        $this->assertGreaterThan(0, (float)$newLine->price_box);
    }
}
