@if(!request()->ajax())
<x-erp-layout title="Editar pedido" subtitle="Puedes actualizar el encabezado del pedido; las líneas y despachos ya registrados se conservan.">
    
    <div class="form-card">
        <form method="POST" action="{{ route('pedidos.update', $order) }}">
            @csrf 
            @method('PUT')

            {{-- Datos del Encabezado del Pedido --}}
            <div class="form-grid mb-4">
                <div class="form-group">
                    <label class="form-label">Número pedido</label>
                    <input name="number" class="form-control" value="{{ old('number', $order->number) }}" required placeholder="PED-0001">
                </div>

                <div class="form-group">
                    <label class="form-label">Cliente</label>
                    <select name="customer_id" class="form-control" required>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $order->customer_id) == $customer->id)>
                                {{ $customer->trade_name ?: $customer->business_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Sala</label>
                    <select name="store_id" class="form-control">
                        <option value="">Sin sala específica</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" @selected(old('store_id', $order->store_id) == $store->id)>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha pedido</label>
                    <input type="date" name="ordered_on" class="form-control" value="{{ old('ordered_on', $order->ordered_on->format('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha entrega</label>
                    <input type="date" name="delivery_on" class="form-control" value="{{ old('delivery_on', $order->delivery_on?->format('Y-m-d')) }}">
                </div>
            </div>

            {{-- Observaciones --}}
            <div class="form-group mb-4">
                <label class="form-label">Observaciones</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Notas adicionales del pedido...">{{ old('notes', $order->notes) }}</textarea>
            </div>

            {{-- Botones de Acción --}}
            <div class="form-actions">
                <a href="/pedidos" class="btn btn-outline-warning">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>

</x-erp-layout>