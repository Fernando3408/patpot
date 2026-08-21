---
paths:
  - app/Models/Input.php
  - 'app/Models/{Product,Production,OrderLine,ShipmentLine}.php'
---

# Models

## Projected input stock
Projected stock must include on-hand stock and transit, then subtract the requirements of every open production order (planned or in progress). Closed production orders are already reflected in physical stock and must not remain committed.

## Boxes are whole units
All finished-product box quantities are whole units: product stock, production planned/actual quantities, order lines, dispatched quantities, and shipment lines. Do not reintroduce decimal boxes; only raw-material quantities may be fractional when their unit permits it.
