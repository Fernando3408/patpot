@if(!request()->ajax())
<x-erp-layout title="Editar compra" subtitle="Puedes actualizar los datos generales de la orden. Las recepciones y sus líneas se conservan.">
    <div class="form-card">
        <form method="POST" action="{{ route('compras.update', $purchase) }}">
            @csrf 
            @method('PUT')

            {{-- Datos de la Orden de Compra --}}
            <div class="form-grid mb-4">
                <div class="form-group">
                    <label class="form-label">Número OC</label>
                    <input name="number" class="form-control" value="{{ old('number', $purchase->number) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Proveedor</label>
                    <select name="supplier_id" class="form-control" required>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchase->supplier_id) == $supplier->id)>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha emisión</label>
                    <input type="date" name="ordered_on" class="form-control" value="{{ old('ordered_on', $purchase->ordered_on->format('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Entrega estimada</label>
                    <input type="date" name="expected_on" class="form-control" value="{{ old('expected_on', $purchase->expected_on?->format('Y-m-d')) }}">
                </div>
            </div>

            {{-- Observaciones --}}
            <div class="form-group mb-4">
                <label class="form-label">Observaciones</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $purchase->notes) }}</textarea>
            </div>

            {{-- Botones de Acción --}}
            <div class="form-actions">
                <a href="/compras" class="btn btn-outline-warning">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@if(!request()->ajax())
</x-erp-layout>