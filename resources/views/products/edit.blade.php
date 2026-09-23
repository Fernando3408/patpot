<form method="POST" action="/productos/{{ $product->id }}">
    @csrf
    @method('PUT')

    <h3 class="text-sm" style="margin-top:0;">Identificación</h3>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Nombre del producto</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">SKU</label>
            <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Gramos por unidad</label>
            <input type="number" name="grams" class="form-control" value="{{ old('grams', (int) $product->grams) }}" step="1" min="0" required>
        </div>
        <div class="form-group">
            <label class="form-label">Unidades por caja</label>
            <input type="number" name="units_per_box" class="form-control" value="{{ old('units_per_box', (int) $product->units_per_box) }}" step="1" min="1" required>
        </div>
    </div>

    <h3 class="text-sm">Inventario y Precios</h3>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Stock en cajas</label>
            <input type="number" name="stock_boxes" class="form-control" value="{{ old('stock_boxes', (int) $product->stock_boxes) }}" step="1" min="0" required>
        </div>
        <div class="form-group">
            <label class="form-label">Stock mínimo (cajas)</label>
            <input type="number" name="min_stock_boxes" class="form-control" value="{{ old('min_stock_boxes', (int) $product->min_stock_boxes) }}" step="1" min="0" required>
        </div>
        <div class="form-group">
            <label class="form-label">Precio venta por caja ($)</label>
            <input type="number" name="sale_price_box" class="form-control" value="{{ old('sale_price_box', (int) $product->sale_price_box) }}" step="1" min="0" required>
        </div>
        <div class="form-group">
            <label class="form-label">Costo piso por caja ($)</label>
            <input type="number" name="production_cost" class="form-control" value="{{ old('production_cost', $product->production_cost) }}" placeholder="Vacío = costo calculado" step="1" min="0">
            <p class="form-hint">Si se define, reemplaza el costo calculado por receta.</p>
        </div>
        <div class="form-group">
            <label class="form-label">Estado</label>
            <select name="status" class="form-control" required>
                <option value="active" @selected(old('status', $product->status) === 'active')>Activo</option>
                <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactivo</option>
            </select>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
</form>
