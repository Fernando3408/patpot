<?php

namespace Database\Seeders;

use App\Models\ChatKnowledge;
use Illuminate\Database\Seeder;

class ChatKnowledgeArchitectureSeeder extends Seeder
{
    public function run(): void
    {
        $knowledge = [
            // === ARQUITECTURA ===
            ['category' => 'Arquitectura', 'key' => 'Stack', 'content' => 'Laravel 13.25, PHP 8.4, MySQL, Vite + Blade, CSS custom (Nova/Metronic 5). Timezone: America/Santiago. Locale: es_CL.'],
            ['category' => 'Arquitectura', 'key' => 'Tablas', 'content' => '22 tablas: products, inputs, recipes, suppliers, customers, stores, prices, purchases, purchase_lines, productions, orders, order_lines, shipments, shipment_lines, retail, inventory_movements, audit_logs, tasks, users, roles, chat_conversations, chat_messages, chat_knowledge.'],
            ['category' => 'Arquitectura', 'key' => 'Modelos', 'content' => '22 modelos Eloquent. SoftDeletes en: products, suppliers, inputs, customers, stores. Relaciones: Supplier→Input→Recipe→Product, Customer→Store→Retail, Order→OrderLine→Product, Purchase→PurchaseLine→Input.'],
            ['category' => 'Arquitectura', 'key' => 'Controladores', 'content' => '18 controladores: ProductController, InputController, RecipeController, SupplierController, CustomerController, StoreController, PriceController, PurchaseController, ProductionController, OrderController, RetailController, TaskController, InventoryMovementController, AuditLogController, AdminController, TrashController, ChatController, TelegramController.'],
            ['category' => 'Arquitectura', 'key' => 'Servicios', 'content' => 'InventoryService (receivePurchase, closeProduction, dispatchOrder), AlertService (alertas del sistema), AuditService (registro de auditoría), ChatbotService (asistente IA con Groq API + fallback local).'],
            ['category' => 'Arquitectura', 'key' => 'Rutas', 'content' => 'Rutas principales: /productos, /insumos, /recetas, /proveedores, /clientes, /salas, /precios, /compras, /produccion, /pedidos, /retail, /tareas, /movimientos, /auditoria, /admin, /chat. Todas bajo middleware auth.'],

            // === FLUJOS ===
            ['category' => 'Flujos', 'key' => 'Inventario', 'content' => 'FLUJO: COMPRA (insumos suben) → PRODUCCIÓN (insumos bajan, productos suben) → DESPACHO (productos bajan). Cada paso registra inventory_movements.'],
            ['category' => 'Flujos', 'key' => 'Compra', 'content' => 'CREAR compra → AGREGAR líneas → Guardar incrementa TRANSITO → RECIBIR baja transito y sube STOCK. Estados: pending, received, partial. No editar si ya recibida.'],
            ['category' => 'Flujos', 'key' => 'Producción', 'content' => 'CREAR producción con producto y cajas → CERRAR descuenta insumos según receta y suma stock producto. Maquila NO descuenta. Envases NO están en recetas.'],
            ['category' => 'Flujos', 'key' => 'Despacho', 'content' => 'CREAR pedido con cliente y tienda → AGREGAR líneas → DESPACHAR descuenta stock producto, crea shipment. Precio viene de prices (cliente+producto).'],

            // === REGLAS DE NEGOCIO ===
            ['category' => 'Reglas', 'key' => 'Costo piso', 'content' => 'Campo production_cost en products. Si está definido, reemplaza costo calculado por receta. Se edita manualmente, NO es automático ni retroactivo.'],
            ['category' => 'Reglas', 'key' => 'Maquila', 'content' => 'Insumo tipo SERVICIO (~$3.400). Incluido en recetas pero NO descuenta stock al cerrar producción. Se identifica porque type="service" en tabla inputs.'],
            ['category' => 'Reglas', 'key' => 'Envases', 'content' => 'Insumos tipo MATERIAL con categoría "Envases". NO van en recetas. Se descuentan automáticamente al DESPACHAR un pedido (deductPackaging).'],
            ['category' => 'Reglas', 'key' => 'Precios', 'content' => 'Cada cliente tiene precios negociados por producto en tabla prices. Si no hay precio especial, se usa sale_price_box del producto. Sin descuentos generales.'],
            ['category' => 'Reglas', 'key' => 'Números', 'content' => 'Enteros para cajas, cantidades. Decimales para recetas e insumos (hasta 3). qty_per_box muestra hasta 3 decimales sin ceros finales ni coma.'],
            ['category' => 'Reglas', 'key' => 'UI', 'content' => 'Sidebar izquierdo 230px oscuro. Todo se actualiza por AJAX sin recargar página. Edición inline con data-field. SweetAlert2 para confirmaciones. DataTables para tablas.'],
            ['category' => 'Reglas', 'key' => 'Roles', 'content' => 'admin: acceso total. administrativo: edición. produccion: solo lectura. ventas: solo lectura. Solo admin puede gestionar usuarios.'],
            ['category' => 'Reglas', 'key' => 'Papelera', 'content' => 'Eliminados van a papelera (SoftDeletes). Se pueden restaurar o eliminar permanentemente. Registros con dependencias no se pueden eliminar.'],

            // === PENDIENTES ===
            ['category' => 'Pendientes', 'key' => 'Gestión', 'content' => 'Costo variable mensual. Asignar costo de gestión por producto cada mes. Pendiente de implementar.'],
            ['category' => 'Pendientes', 'key' => 'Flete', 'content' => 'Costo por viaje. Registrar costo de flete y cajas por envío. Pendiente de implementar.'],
            ['category' => 'Pendientes', 'key' => 'WhatsApp', 'content' => 'Integración con WhatsApp Business API o Twilio para notificaciones. Pendiente.'],
        ];

        foreach ($knowledge as $item) {
            ChatKnowledge::updateOrCreate(
                ['category' => $item['category'], 'key' => $item['key']],
                ['content' => $item['content'], 'active' => true]
            );
        }
    }
}
