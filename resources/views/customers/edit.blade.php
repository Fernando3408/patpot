@if(!request()->ajax())
<x-erp-layout title="Editar cliente - PatPot" subtitle="Modifica los datos del cliente en el sistema.">
@endif

    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Editar Cliente</h2>
        </div>
        <div class="card__body">
            <form method="POST" action="{{ route('customers.update', $customer) }}">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="code">Código *:</label>
                        <input id="code" type="text" name="code" class="form-control" value="{{ old('code', $customer->code) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="business_name">Razón social *:</label>
                        <input id="business_name" type="text" name="business_name" class="form-control" value="{{ old('business_name', $customer->business_name) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="trade_name">Nombre de fantasía:</label>
                        <input id="trade_name" type="text" name="trade_name" class="form-control" value="{{ old('trade_name', $customer->trade_name) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rut">RUT:</label>
                        <input id="rut" type="text" name="rut" class="form-control" value="{{ old('rut', $customer->rut) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="type">Tipo:</label>
                        <input id="type" type="text" name="type" class="form-control" value="{{ old('type', $customer->type) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="channel">Canal:</label>
                        <input id="channel" type="text" name="channel" class="form-control" value="{{ old('channel', $customer->channel) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact">Contacto:</label>
                        <input id="contact" type="text" name="contact" class="form-control" value="{{ old('contact', $customer->contact) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Correo electrónico:</label>
                        <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="payment_terms">Condición de pago:</label>
                        <input id="payment_terms" type="text" name="payment_terms" class="form-control" value="{{ old('payment_terms', $customer->payment_terms) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="discount">Descuento (%) *:</label>
                        <input id="discount" type="number" step="0.01" min="0" max="100" name="discount" class="form-control" value="{{ old('discount', $customer->discount) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="status">Estado *:</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="1" {{ old('status', $customer->status) == 1 ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('status', $customer->status) == 0 ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions" style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-warning">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

@if(!request()->ajax())
</x-erp-layout>
@endif