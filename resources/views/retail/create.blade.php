<x-erp-layout title="Nuevo registro retail" subtitle="Registra el stock, tránsito y métricas de un producto en una sala.">
    


    <div class="card">
        <div class="card__body">
            <form method="POST" action="/retail">
                @csrf

                <div class="form-grid">

                    <div class="form-group">
                        <label class="form-label" for="store_id">Sala *:</label>
                        <select id="store_id" name="store_id" class="form-input" required>
                            <option value="">Seleccione una sala</option>
                            @foreach($stores as $store)
                                <option
                                    value="{{ $store->id }}"
                                    {{ old('store_id') == $store->id ? 'selected' : '' }}
                                >
                                    {{ $store->code }} — {{ $store->customer?->trade_name ?? $store->customer?->business_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="product_id">Producto *:</label>
                        <select id="product_id" name="product_id" class="form-input" required>
                            <option value="">Seleccione un producto</option>
                            @foreach($products as $product)
                                <option
                                    value="{{ $product->id }}"
                                    {{ old('product_id') == $product->id ? 'selected' : '' }}
                                >
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="cataloged">Catalogado en esta sala *:</label>
                        <select id="cataloged" name="cataloged" class="form-input" required>
                            <option value="1" {{ old('cataloged', '1') == '1' ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ old('cataloged') == '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="stock_units">Stock (unidades):</label>
                        <input id="stock_units" type="number" step="1" min="0" name="stock_units" class="form-input" value="{{ old('stock_units', 0) !== null ? (int)old('stock_units', 0) : 0 }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="transit_units">En tránsito (unidades):</label>
                        <input id="transit_units" type="number" step="1" min="0" name="transit_units" class="form-input" value="{{ old('transit_units', 0) !== null ? (int)old('transit_units', 0) : 0 }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="weekly_sales">Venta semanal (unidades):</label>
                        <input id="weekly_sales" type="number" step="1" min="0" name="weekly_sales" class="form-input" value="{{ old('weekly_sales', 0) !== null ? (int)old('weekly_sales', 0) : 0 }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="min_stock">Stock mínimo (unidades):</label>
                        <input id="min_stock" type="number" step="1" min="0" name="min_stock" class="form-input" value="{{ old('min_stock', 0) !== null ? (int)old('min_stock', 0) : 0 }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reorder_point">Punto de reposición (unidades):</label>
                        <input id="reorder_point" type="number" step="1" min="0" name="reorder_point" class="form-input" value="{{ old('reorder_point', 0) !== null ? (int)old('reorder_point', 0) : 0 }}">
                    </div>

                </div>

                <div class="form-actions-end">
                    <button type="submit" class="btn btn-primary">
                        Guardar registro
                    </button>
                </div>

            </form>
        </div>
    </div>

</x-erp-layout>