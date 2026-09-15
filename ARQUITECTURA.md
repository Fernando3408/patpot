# ARQUITECTURA DEL SISTEMA ERP - PATPOT

## Visión General
ERP completo para **PatPot** (papas fritas gourmet chilenas). Gestión integral de inventario, compras, producción, pedidos, retail, y asistente IA.

**Stack:** Laravel 13.25 | PHP 8.4 | MySQL | Vite + Blade | CSS custom (Nova/Metronic 5)

---

## 1. Estructura de Directorios

```
patpot/
├── app/
│   ├── Console/Commands/       # ChatTest artisan command
│   ├── Http/Controllers/       # 18 controladores
│   ├── Models/                 # 22 modelos Eloquent
│   ├── Services/               # 3 servicios (AlertService, AuditService, InventoryService, ChatbotService)
│   └── Traits/                 # ValidatesWithLineFormatting
├── database/
│   ├── migrations/             # 22 migraciones
│   └── seeders/                # DatabaseSeeder + ChatKnowledgeSeeder
├── resources/
│   ├── lang/es/                # Validación y paginación en español
│   └── views/                  # 17 directorios de vistas Blade
├── routes/web.php              # Todas las rutas HTTP
└── config/services.php         # Gemini API + Telegram token
```

---

## 2. Base de Datos (22 tablas)

### Core de Negocio
| Tabla | Descripción | Relaciones clave |
|-------|-------------|------------------|
| `products` | Productos terminados (SKU, nombre, gramos, unidades/caja, stock, precio) | → recipes, order_lines, productions, retail, prices |
| `inputs` | Insumos (materia prima, envases, servicios) | → recipes, purchase_lines, inventory_movements |
| `recipes` | Recetas: qué insumo y cuánto por caja de producto | product_id + input_id UNIQUE |
| `suppliers` | Proveedores | → inputs, purchases |
| `customers` | Clientes | → stores, prices, orders |
| `stores` | Tiendas/salas por cliente | → retail |
| `prices` | Precios negociados por cliente+producto | customer_id + product_id |

### Operaciones
| Tabla | Descripción | Relaciones clave |
|-------|-------------|------------------|
| `purchases` | Órdenes de compra a proveedores | → purchase_lines |
| `purchase_lines` | Líneas de compra (insumo, cantidad, costo) | purchase_id, input_id |
| `productions` | Órdenes de producción | product_id |
| `orders` | Pedidos de clientes | → order_lines, shipments |
| `order_lines` | Líneas de pedido (producto, cajas, precio) | order_id, product_id |
| `shipments` | Despachos realizados | → shipment_lines |
| `shipment_lines` | Líneas de despacho | shipment_id, order_line_id |

### Control
| Tabla | Descripción |
|-------|-------------|
| `retail` | Stock y ventas por tienda-producto |
| `inventory_movements` | Trazabilidad de cada cambio de stock |
| `audit_logs` | Registro de acciones del sistema |
| `tasks` | Tareas pendientes por usuario |
| `chat_conversations` | Conversaciones del asistente IA |
| `chat_messages` | Mensajes del chat (user/assistant) |
| `chat_knowledge` | Base de conocimiento del asistente |

### Sistema
| Tabla | Descripción |
|-------|-------------|
| `users` | Usuarios del sistema |
| `roles` | Roles (admin, administrativo, produccion, ventas) |
| `model_has_roles` | Relación usuario-rol (pivot) |

---

## 3. Diagrama de Relaciones (ER)

```
Supplier ──1:N──> Input ──N:M──> Product (via Recipe)
                                      │
                              ┌───────┼────────┐
                              │       │        │
                          1:N │    1:N │     1:N │
                              v       v        v
                          Retail   Price   Production
                            │
                         N:1│
                            v
                         Store
                            │
                         N:1│
                            v
                        Customer ──1:N──> Order ──1:N──> OrderLine ──N:1──> Product
                                            │
                                         1:N│
                                            v
                                      Shipment ──1:N──> ShipmentLine

Input ──1:N──> PurchaseLine ──N:1──> Purchase ──N:1──> Supplier
Input/Product ──1:N──> InventoryMovement
User ──1:N──> AuditLog
User ──1:N──> ChatConversation ──1:N──> ChatMessage
```

---

## 4. Controladores y Rutas

### Módulo Inventario
| Ruta | Controller | Función |
|------|------------|---------|
| `/productos` | ProductController | CRUD productos terminados |
| `/insumos` | InputController | CRUD insumos (material/servicio) |
| `/recetas` | RecipeController | CRUD recetas por producto |
| `/proveedores` | SupplierController | CRUD proveedores |

### Módulo Compras
| Ruta | Controller | Función |
|------|------------|---------|
| `/compras` | PurchaseController | CRUD compras + recepción |

### Módulo Producción
| Ruta | Controller | Función |
|------|------------|---------|
| `/produccion` | ProductionController | CRUD producciones + cierre |

### Módulo Ventas
| Ruta | Controller | Función |
|------|------------|---------|
| `/clientes` | CustomerController | CRUD clientes |
| `/salas` | StoreController | CRUD tiendas/salas |
| `/precios` | PriceController | CRUD precios por cliente |
| `/pedidos` | OrderController | CRUD pedidos + despacho |
| `/retail` | RetailController | Seguimiento stock retail |

### Módulo Control
| Ruta | Controller | Función |
|------|------------|---------|
| `/tareas` | TaskController | CRUD tareas |
| `/movimientos` | InventoryMovementController | Solo lectura |
| `/auditoria` | AuditLogController | Solo lectura |

### Asistente IA
| Ruta | Controller | Función |
|------|------------|---------|
| `/chat` | ChatController | Chat web (index) |
| `/chat/send` | ChatController | Enviar mensaje (AJAX) |
| `/api/telegram/webhook` | TelegramController | Webhook Telegram |

### Admin
| Ruta | Controller | Función |
|------|------------|---------|
| `/admin` | AdminController | Gestionar usuarios |
| `/admin/papelera` | TrashController | Papelera (SoftDeletes) |

---

## 5. Servicios

### InventoryService
- **receivePurchase()**: Recibe compra → actualiza stock y transito de insumos
- **closeProduction()**: Cierra producción → descuenta insumos según receta, suma stock producto
- **dispatchOrder()**: Despacha pedido → descuenta stock producto, crea envío
- **updatePurchaseLines()**: Edición transaccional de líneas de compra
- **updateOrderLines()**: Edición transaccional de líneas de pedido

### AlertService
- Insumos críticos (stock <= 0)
- Compras atrasadas
- Pedidos atrasados
- Quiebres retail
- Tareas vencidas
- Alertas 50% (stock <= 50% del safety_stock)

### AuditService
- Registra cada acción importante en `audit_logs`
- Usuario + acción + descripción + timestamp

### ChatbotService
- API Groq (Llama 3.3 70B) para IA — gratis, sin tarjeta
- Fallback local cuando Groq falla (responde desde la BD directamente)
- Historial de conversaciones (chat_conversations + chat_messages)
- Base de conocimiento (chat_knowledge) + lectura directa de ARQUITECTURA.md
- Contexto en tiempo real: productos, recetas, insumos, clientes, proveedores, operaciones

---

## 6. Modelo de Negocio

### Flujo de Inventario
```
COMPRA (insumos suben) → PRODUCCIÓN (insumos bajan, productos suben) → DESPACHO (productos bajan)
```

### Reglas de Costo
- **Costo piso**: Campo `production_cost` en products. Si está definido, reemplaza el costo calculado por receta. Se edita manualmente.
- **Maquila**: Insumo tipo servicio. Incluido en recetas pero NO descuenta stock al cerrar producción.
- **Envases**: Insumo tipo material, categoría "Envases". NO van en recetas. Se descuentan al DESPACHAR.
- **Gestión**: Costo variable mensual. Pendiente de implementar.
- **Flete**: Costo por viaje. Pendiente de implementar.

### Precios
- Cada cliente tiene precios negociados por producto en `prices`
- Si no hay precio especial, se usa `sale_price_box` del producto
- Sin descuentos generales por cliente

### Alertas
- **Crítico**: stock <= 0
- **Atención**: stock > 0 pero <= safety_stock
- **Alerta 50%**: stock <= 50% del safety_stock (solo materiales)

---

## 7. Asistente IA

### Tablas de Memoria
- **chat_conversations**: Una fila por sesión de chat del usuario
- **chat_messages**: Cada mensaje (user o assistant) con timestamp y tokens
- **chat_knowledge**: Base de conocimiento estática con datos del dominio

### Flujo
1. Usuario envía mensaje → se guarda en `chat_messages`
2. Se construye contexto: datos en tiempo real + knowledge base + historial
3. Se envía a Gemini API con todo el contexto
4. Respuesta se guarda en `chat_messages`

### Knowledge Base (chat_knowledge)
Categorías: Empresa, Inventario, Compras, Producción, Pedidos, Negocio, Retail, Admin, Tareas.

---

## 8. Diseño UI

- **Layout**: Sidebar izquierdo (230px, oscuro) + contenido principal
- **Estilo**: Nova/Metronic 5, font IBM Plex Sans
- **Colores**: `--primary: #df6403` (naranja), `--topbar-bg: #2c2e39`
- **Iconos**: Lucide Icons
- **Tablas**: DataTables 1.13.7 con inicialización client-side
- **Alertas**: SweetAlert2
- **Charts**: Chart.js 4
- **Chat widget**: Flotante (esquina inferior derecha), estilo Facebook
- **Edición inline**: Solo celdas con `data-field`, AJAX save

---

## 9. Convenciones

- **Números**: Enteros excepto recetas e insumos (hasta 3 decimales)
- **qty_per_box**: Máximo 3 decimales, sin ceros ni comas finales
- **Locale**: `America/Santiago`, `es_CL`
- **Validación**: Mensajes en español
- **SoftDeletes**: En products, suppliers, inputs, customers, stores
- **Auditoría**: Todas las acciones importantes se registran
- **AJAX**: Operaciones actualizan UI sin recarga de página

---

## 10. Pendiente

- [ ] Gestión (asignación de costo variable mensual por producto)
- [ ] Flete/transporte (costo por viaje + cajas por envío)
- [ ] Integración WhatsApp (Business API o Twilio)
- [ ] Permisos granulares por rol (actualmente solo admin escribe)
- [ ] Logo/marca en sidebar
- [ ] Producción APP_URL con dominio real
