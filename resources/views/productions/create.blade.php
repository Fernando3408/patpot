<x-erp-layout title="Nueva producción" subtitle="Al cerrar la orden, se descontarán los insumos de la receta y se sumará el producto terminado.">
    <form method="POST" action="/produccion">
        @csrf

        {{-- Datos de la Orden de Producción --}}
        <div class="card p-4 mb-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label">Número OP</label>
                        <input type="text" name="number" class="form-control @error('number') is-invalid @enderror" value="{{ old('number') }}" placeholder="OP-0001" required>
                        @error('number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label">Producto</label>
                        <select name="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                            <option value="">Seleccione</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                    {{ $product->name }} · capacidad actual: {{ $product->production_capacity !== null ? number_format($product->production_capacity, 0, ',', '.') : 'sin receta' }} cajas
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
                        <label class="form-label">Cajas planificadas</label>
                        <input type="number" step="1" min="1" name="planned_boxes" class="form-control @error('planned_boxes') is-invalid @enderror" value="{{ old('planned_boxes') }}" required>
                        @error('planned_boxes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <label class="form-label">Fecha planificada</label>
                        <input type="date" name="planned_on" class="form-control @error('planned_on') is-invalid @enderror" value="{{ old('planned_on', today()->format('Y-m-d')) }}" required>
                        @error('planned_on')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Observaciones --}}
        <div class="card p-4 mb-4">
            <div class="form-group mb-0">
                <label class="form-label">Observaciones</label>
                <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Botones de Control --}}
        <div class="form-actions d-flex justify-content-between align-items-center">
            <a href="/produccion" class="btn btn-outline-warning">
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                Guardar planificación
            </button>
        </div>
    </form>
</x-erp-layout>