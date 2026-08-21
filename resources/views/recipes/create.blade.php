<x-erp-layout title="Agregar insumo a receta" subtitle="Asigna insumos y define sus cantidades por caja para construir la receta de un producto.">
    <div class="form-card">
        
        @if ($products->isNotEmpty() && $inputs->isNotEmpty())
            <form method="POST" action="/recetas">
                @csrf

                <div class="form-grid mb-4">
                    {{-- Selección de Producto --}}
                    <div class="form-group">
                        <label for="product_id" class="form-label">Producto</label>
                        <select id="product_id" name="product_id" class="form-control" required>
                            <option value="">Selecciona un producto</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Selección de Insumo --}}
                    <div class="form-group">
                        <label for="input_id" class="form-label">Insumo</label>
                        <select id="input_id" name="input_id" class="form-control" required>
                            <option value="">Selecciona un insumo</option>
                            @foreach ($inputs as $input)
                                <option value="{{ $input->id }}" @selected(old('input_id') == $input->id)>
                                    {{ $input->name }} ({{ $input->code }}) · {{ $input->unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Cantidad por Caja --}}
                    <div class="form-group">
                        <label for="qty_per_box" class="form-label">Cantidad por caja</label>
                        <input
                            type="number"
                            id="qty_per_box"
                            name="qty_per_box"
                            class="form-control"
                            min="0.01"
                            step="0.01"
                            value="{{ old('qty_per_box') }}"
                            placeholder="Ej: 1.50"
                            required
                        >
                    </div>
                </div>

                {{-- Botones de Acción --}}
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Agregar insumo
                    </button>
                </div>
            </form>
        @else
            <div class="data-table-empty mb-4">
                <p>Necesitas al menos un producto activo y un insumo activo para crear una receta.</p>
            </div>
            <div class="form-actions">
        @endif

    </div>
</x-erp-layout>