@if(!request()->ajax())
<x-erp-layout title="Editar producción" subtitle="Solo se pueden modificar órdenes que todavía no han sido cerradas.">
    <form method="POST" action="{{ route('produccion.update', $production) }}">
        @csrf 
        @method('PUT')

        {{-- Datos de la Orden de Producción --}}
        <div>
            <label>
                Número OP
                <input name="number" value="{{ old('number', $production->number) }}" required>
            </label>

            <label>
                Producto
                <select name="product_id" required>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(old('product_id', $production->product_id) == $product->id)>
                            {{ $product->name }} · capacidad: {{ $product->production_capacity ?? 'sin receta' }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                Cajas planificadas
                <input type="number" step="0.0001" name="planned_boxes" value="{{ old('planned_boxes', $production->planned_boxes) }}" required>
            </label>

            <label>
                Fecha planificada
                <input type="date" name="planned_on" value="{{ old('planned_on', $production->planned_on->format('Y-m-d')) }}" required>
            </label>
        </div>

        {{-- Observaciones --}}
        <label>
            Observaciones
            <textarea name="notes" rows="3">{{ old('notes', $production->notes) }}</textarea>
        </label>

        {{-- Botones de Control --}}
        <div>
            <a href="/produccion">
                Cancelar
            </a>
            <button type="submit">
                Guardar cambios
            </button>
        </div>
    </form>
@if(!request()->ajax())
</x-erp-layout>