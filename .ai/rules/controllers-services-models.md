---
paths:
  - 'app/{Http/Controllers,Services,Models}/**'
---

# Controllers Services Models

## Precios por cliente sin descuentos comerciales
Los clientes no aplican descuento general automático y los pedidos no aplican descuento por línea. El precio comercial se pacta por cliente y producto en prices; pedidos, despachos y márgenes deben usar ese precio de línea sin descuentos.

## Pedidos despachados no se editan
Cuando un pedido tiene al menos una línea con dispatched_boxes > 0, el pedido completo deja de ser editable. No mostrar botón Editar y bloquear edit/update por ruta directa; los despachos históricos son la evidencia operativa.

## Precios pactados sin descuentos comerciales
La venta se calcula con precio pactado por cliente/producto o precio base del producto. No aplicar descuentos comerciales automáticos: ni customer.discount ni order_lines.discount_pct deben afectar totales, despachos o márgenes; conservar columnas antiguas solo como compatibilidad y en 0.
