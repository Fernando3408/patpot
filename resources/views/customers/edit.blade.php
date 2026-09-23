<form method="POST" action="{{ route('customers.update', $customer) }}">
    @csrf
    @method('PUT')

    <h3 class="text-sm" style="margin-top:0;">Datos del Cliente</h3>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Código *</label>
            <input type="text" name="code" class="form-control" value="{{ old('code', $customer->code) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Razón social *</label>
            <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $customer->business_name) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Nombre de fantasía</label>
            <input type="text" name="trade_name" class="form-control" value="{{ old('trade_name', $customer->trade_name) }}">
        </div>
        <div class="form-group">
            <label class="form-label">RUT</label>
            <input type="text" name="rut" class="form-control" value="{{ old('rut', $customer->rut) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Tipo</label>
            <input type="text" name="type" class="form-control" value="{{ old('type', $customer->type) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Canal</label>
            <input type="text" name="channel" class="form-control" value="{{ old('channel', $customer->channel) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Contacto</label>
            <input type="text" name="contact" class="form-control" value="{{ old('contact', $customer->contact) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Condición de pago</label>
            <input type="text" name="payment_terms" class="form-control" value="{{ old('payment_terms', $customer->payment_terms) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Estado *</label>
            <select name="status" class="form-control" required>
                <option value="1" {{ old('status', $customer->status) == 1 ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ old('status', $customer->status) == 0 ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
</form>
