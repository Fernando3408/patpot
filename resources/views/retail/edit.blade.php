@if(!request()->ajax())
<x-erp-layout title="Editar registro retail" subtitle="Modifica los datos del stock, tránsito y parámetros de quiebre en punto de venta.">
    
    <form method="POST" action="/retail/{{ $retail->id }}">
        @csrf
        @method('PUT')

        {{-- Datos principales --}}
        <div class="card p-4 mb-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="store_id">Sala</label>
                        <select id="store_id" name="store_id" class="form-control @error('store_id') is-invalid @enderror" required>
                            <option value="">Seleccione una sala</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" @selected(old('store_id', $retail->store_id) == $store->id)>
                                    {{ $store->code }} — {{ $store->customer?->trade_name ?? $store->customer?->business_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('store_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="product_id">Producto</label>
                        <select id="product_id" name="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                            <option value="">Seleccione un producto</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id', $retail->product_id) == $product->id)>
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="cataloged">Catalogado en esta sala</label>
                        <select id="cataloged" name="cataloged" class="form-control @error('cataloged') is-invalid @enderror" required>
                            <option value="1" @selected(old('cataloged', $retail->cataloged) == 1)>Sí</option>
                            <option value="0" @selected(old('cataloged', $retail->cataloged) == 0)>No</option>
                        </select>
                        @error('cataloged')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="stock_units">Stock (unidades)</label>
                        <input id="stock_units" type="number" step="1" min="0" name="stock_units" class="form-control @error('stock_units') is-invalid @enderror" value="{{ old('stock_units', $retail->stock_units !== null ? (int)$retail->stock_units : '') }}" required>
                        @error('stock_units')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="transit_units">En tránsito (unidades)</label>
                        <input id="transit_units" type="number" step="1" min="0" name="transit_units" class="form-control @error('transit_units') is-invalid @enderror" value="{{ old('transit_units', $retail->transit_units !== null ? (int)$retail->transit_units : '') }}" required>
                        @error('transit_units')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="weekly_sales">Venta semanal (unidades)</label>
                        <input id="weekly_sales" type="number" step="1" min="0" name="weekly_sales" class="form-control @error('weekly_sales') is-invalid @enderror" value="{{ old('weekly_sales', $retail->weekly_sales !== null ? (int)$retail->weekly_sales : '') }}" required>
                        @error('weekly_sales')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="min_stock">Stock mínimo (unidades)</label>
                        <input id="min_stock" type="number" step="1" min="0" name="min_stock" class="form-control @error('min_stock') is-invalid @enderror" value="{{ old('min_stock', $retail->min_stock !== null ? (int)$retail->min_stock : '') }}" required>
                        @error('min_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label" for="reorder_point">Punto de reposición (unidades)</label>
                        <input id="reorder_point" type="number" step="1" min="0" name="reorder_point" class="form-control @error('reorder_point') is-invalid @enderror" value="{{ old('reorder_point', $retail->reorder_point !== null ? (int)$retail->reorder_point : '') }}" required>
                        @error('reorder_point')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Botones de Control --}}
        <div class="form-actions d-flex justify-content-between align-items-center">
            <button type="submit" class="btn btn-primary">
                Guardar cambios
            </button>
        </div>
    </form>
@if(!request()->ajax())
</x-erp-layout>