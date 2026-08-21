@if(!request()->ajax())
<x-erp-layout title="Editar producto" subtitle="Actualiza la información base, precios o estado del producto registrado.">
    <div class="form-card">
        <form method="POST" action="/productos/{{ $product->id }}">
            @csrf
            @method('PUT')

            {{-- Información Principal --}}
            <div class="form-grid mb-4">
                <div class="form-group">
                    <label for="name" class="form-label">Nombre del producto</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="{{ old('name', $product->name) }}"
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
                        value="{{ old('sku', $product->sku) }}"
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
                        value="{{ old('grams', (int) $product->grams) }}"
                        step="1"
                        min="0"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="units_per_box" class="form-label">Unidades por caja</label>
                    <input
                        type="number"
                        id="units_per_box"
                        name="units_per_box"
                        class="form-control"
                        value="{{ old('units_per_box', (int) $product->units_per_box) }}"
                        step="1"
                        min="1"
                        required
                    >
                </div>
            </div>

            {{-- Inventario y Precios --}}
            <div class="form-grid mb-4">
                <div class="form-group">
                    <label for="stock_boxes" class="form-label">Stock en cajas</label>
                    <input
                        type="number"
                        id="stock_boxes"
                        name="stock_boxes"
                        class="form-control"
                        value="{{ old('stock_boxes', (int) $product->stock_boxes) }}"
                        step="1"
                        min="0"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="min_stock_boxes" class="form-label">Stock mínimo (cajas)</label>
                    <input
                        type="number"
                        id="min_stock_boxes"
                        name="min_stock_boxes"
                        class="form-control"
                        value="{{ old('min_stock_boxes', (int) $product->min_stock_boxes) }}"
                        step="1"
                        min="0"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="sale_price_box" class="form-label">Precio venta por caja ($)</label>
                    <input
                        type="number"
                        id="sale_price_box"
                        name="sale_price_box"
                        class="form-control"
                        value="{{ old('sale_price_box', (int) $product->sale_price_box) }}"
                        step="1"
                        min="0"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Estado</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" @selected(old('status', $product->status) === 'active')>
                            Activo
                        </option>
                        <option value="inactive" @selected(old('status', $product->status) === 'inactive')>
                            Inactivo
                        </option>
                    </select>
                </div>
            </div>

            {{-- Botones de Acción --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@if(!request()->ajax())
</x-erp-layout>