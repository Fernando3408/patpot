<?php

namespace Database\Seeders;

use App\Models\ChatKnowledge;
use Illuminate\Database\Seeder;

class ChatKnowledgeSeeder extends Seeder
{
    public function run(): void
    {
        $knowledge = [
            // === EMPRESA ===
            ['category' => 'Empresa', 'key' => 'Nombre', 'content' => 'PatPot - Papas fritas gourmet artesanales. Empresa chilena.'],
            ['category' => 'Empresa', 'key' => 'Productos', 'content' => 'Venden papas fritas gourmet en cajas. Sabores: Sal de Mar, Merkén, etc. Formatos: 45g (individual), 150g (familiar).'],
            ['category' => 'Empresa', 'key' => 'Unidades', 'content' => 'Productos se venden en CAJAS. Insumos se compran en KG, L, o unidades. Cada producto tiene X unidades por caja.'],

            // === INVENTARIO ===
            ['category' => 'Inventario', 'key' => 'Productos', 'content' => 'La tabla products guarda: sku, nombre, gramos, unidades_por_caja, stock_cajas, stock_minimo, precio_venta_caja.'],
            ['category' => 'Inventario', 'key' => 'Insumos', 'content' => 'La tabla inputs guarda: codigo, nombre, categoria, unidad, stock, stock_seguridad, consumo_semanal, dias_lead_time, costo_unitario, proveedor_id. Tipos: material (se descuenta) y servicio (maquila, NO se descuenta).'],
            ['category' => 'Inventario', 'key' => 'Movimientos', 'content' => 'Cada vez que cambia el stock de un insumo o producto, se registra en inventory_movements con tipo (compra/produccion/despacho/ajuste), cantidad, y referencia.'],
            ['category' => 'Inventario', 'key' => 'Alertas', 'content' => 'Nivel critico: stock <= 0. Nivel atencion: stock > 0 pero <= stock_seguridad. Nivel OK: stock > stock_seguridad. Los materiales con stock <= 50% del safety_stock generan alerta.'],

            // === OPERACIONES ===
            ['category' => 'Compras', 'key' => 'Flujo', 'content' => '1) Crear compra con proveedor. 2) Agregar lineas (insumo, cantidad, costo). 3) Al guardar, se incrementa el TRANSITO del insumo. 4) Al RECIBIR, el transito baja y el STOCK sube.'],
            ['category' => 'Compras', 'key' => 'Estados', 'content' => 'Compra puede ser: pending (creada), received (recibida completa), partial (recibida parcialmente). No se puede editar una compra recibida.'],

            ['category' => 'Produccion', 'key' => 'Flujo', 'content' => '1) Crear produccion con producto y cajas planificadas. 2) Al CERRAR, se descuenta insumos segun receta y sube el stock del producto terminado. Las cantidades de insumos son decimales (ej: 0.5 kg).'],
            ['category' => 'Produccion', 'key' => 'Cierre', 'content' => 'El cierre de produccion consume insumos del inventario. Los insumos tipo servicio (maquila) NO se descuentan. El stock del producto terminado sube por las cajas reales.'],

            ['category' => 'Pedidos', 'key' => 'Flujo', 'content' => '1) Crear pedido con cliente y tienda. 2) Agregar lineas (producto, cajas, precio). 3) Al DESPACHAR, se descuenta stock del producto y se crea envio.'],
            ['category' => 'Pedidos', 'key' => 'Precios', 'content' => 'El precio viene de la tabla prices (cliente + producto). Si no hay precio especial, se usa el precio_venta_caja del producto.'],
            ['category' => 'Pedidos', 'key' => 'Despacho', 'content' => 'Al despachar se descuenta el stock del producto terminado. Se genera un shipment con sus shipment_lines.'],

            // === MODELO DE NEGOCIO ===
            ['category' => 'Negocio', 'key' => 'Costo piso', 'content' => 'Cada producto puede tener un production_cost manual. Si está definido, se usa como costo en vez del calculado por receta. Se edita manualmente, no es automático.'],
            ['category' => 'Negocio', 'key' => 'Maquila', 'content' => 'La maquila es un insumo de tipo SERVICIO (~$3.400). Se incluye en recetas pero NO descuenta stock al cerrar producción.'],
            ['category' => 'Negocio', 'key' => 'Envases', 'content' => 'Los envases son insumos tipo MATERIAL con categoría "Envases". NO van en recetas. Se descuentan automáticamente al DESPACHAR un pedido.'],
            ['category' => 'Negocio', 'key' => 'Gestión', 'content' => 'La gestión es un costo variable mensual que se prorratea entre los productos. Pendiente de implementar completamente.'],
            ['category' => 'Negocio', 'key' => 'Flete', 'content' => 'El flete es un costo por viaje. Se cobra por envío. Pendiente de implementar.'],
            ['category' => 'Negocio', 'key' => 'Precio por cliente', 'content' => 'Cada cliente tiene precios negociados por producto en la tabla prices. Si no hay precio, se usa el precio general del producto.'],

            // === RETAIL ===
            ['category' => 'Retail', 'key' => 'Concepto', 'content' => 'Retail es el seguimiento de stock en tiendas de clientes. Cada tienda-producto tiene: stock_units, transit_units, weekly_sales, min_stock, reorder_point.'],
            ['category' => 'Retail', 'key' => 'Quiebre', 'content' => 'Quiebre = stock <= 0 Y transito <= 0. La tienda no tiene producto ni envío pendiente.'],
            ['category' => 'Retail', 'key' => 'Reposición', 'content' => 'Sugerencia de reposición = minimo_stock - stock_actual - transito. Si es positivo, hay que enviar esa cantidad.'],

            // === ADMIN ===
            ['category' => 'Admin', 'key' => 'Roles', 'content' => 'Roles: admin (acceso total), administrativo (edición), produccion (solo lectura), ventas (solo lectura).'],
            ['category' => 'Admin', 'key' => 'Papelera', 'content' => 'Los registros eliminados van a la papelera (SoftDeletes). Se pueden restaurar o eliminar permanentemente.'],
            ['category' => 'Admin', 'key' => 'Auditoría', 'content' => 'Cada acción importante (crear, editar, eliminar) se registra en audit_logs con usuario, acción y descripción.'],

            // === TAREAS ===
            ['category' => 'Tareas', 'key' => 'Sistema', 'content' => 'Las tareas tienen: titulo, responsable, fecha_limite, prioridad (low/medium/high), modulo, estado (pending/completed). Las vencidas aparecen como urgentes.'],
        ];

        foreach ($knowledge as $item) {
            ChatKnowledge::updateOrCreate(
                ['category' => $item['category'], 'key' => $item['key']],
                ['content' => $item['content'], 'active' => true]
            );
        }
    }
}
