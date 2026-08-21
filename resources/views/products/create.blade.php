<x-erp-layout title="Nuevo producto" subtitle="Ingresa los datos base para registrar un nuevo producto en el catálogo.">
    <div class="form-card">
        <form method="POST" action="/productos">
            @csrf

            {{-- Información Principal --}}
            <div class="form-grid mb-4">
                <div class="form-group">
                    <label for="name" class="form-label">Nombre del producto</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="{{ old('name') }}"
                        placeholder="Ej: Papas Chips Sal de Mar 150 g"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="sku" class="form-label">SKU</label>
                    <input
                        type="text"
                        id="sku"
                        name="sku"
                        class="form-control"
                        value="{{ old('sku') }}"
                        placeholder="Ej: PROD-FRU-250"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="grams" class="form-label">Gramos por unidad</label>
                    <input
                        type="number"
                        id="grams"
                        name="grams"
                        class="form-control"
                        value="{{ old('grams') }}"
                        placeholder="250"
                        step="1"
                        min="0"
                    >
                </div>

                <div class="form-group">
                    <label for="units_per_box" class="form-label">Unidades por caja</label>
                    <input
                        type="number"
                        id="units_per_box"
                        name="units_per_box"
                        class="form-control"
                        value="{{ old('units_per_box') }}"
                        placeholder="12"
                        step="1"
                        min="1"
                    >
                </div>
            </div>

            {{-- Inventario y Precios --}}
            <div class="form-grid mb-4">
                <div class="form-group">
                    <label for="stock_boxes" class="form-label">Stock inicial (cajas)</label>
                    <input
                        type="number"
                        id="stock_boxes"
                        name="stock_boxes"
                        class="form-control"
                        value="{{ old('stock_boxes', 0) }}"
                        step="1"
                        min="0"
                    >
                </div>

                <div class="form-group">
                    <label for="min_stock_boxes" class="form-label">Stock mínimo (cajas)</label>
                    <input
                        type="number"
                        id="min_stock_boxes"
                        name="min_stock_boxes"
                        class="form-control"
                        value="{{ old('min_stock_boxes', 0) }}"
                        step="1"
                        min="0"
                    >
                </div>

                <div class="form-group">
                    <label for="sale_price_box" class="form-label">Precio venta por caja ($)</label>
                    <input
                        type="number"
                        id="sale_price_box"
                        name="sale_price_box"
                        class="form-control"
                        value="{{ old('sale_price_box') }}"
                        placeholder="15000"
                        step="1"
                        min="0"
                    >
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Estado</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active" @selected(old('status') === 'active')>Activo</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactivo</option>
                    </select>
                </div>
            </div>

            {{-- Botones de Acción --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Guardar producto
                </button>
            </div>
        </form>
    </div>
</x-erp-layout>