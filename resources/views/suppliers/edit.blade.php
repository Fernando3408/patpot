<form method="POST" action="{{ route('proveedores.update', $supplier) }}">
    @csrf
    @method('PUT')

    <h3 class="text-sm" style="margin-top:0;">Datos del Proveedor</h3>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Nombre *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">RUT</label>
            <input type="text" name="rut" class="form-control" value="{{ old('rut', $supplier->rut) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Nombre de contacto</label>
            <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name', $supplier->contact_name) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Teléfono</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Lead time (días)</label>
            <input type="number" min="0" name="lead_time_days" class="form-control" value="{{ old('lead_time_days', $supplier->lead_time_days) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Condiciones de pago</label>
            <input type="text" name="payment_terms" class="form-control" value="{{ old('payment_terms', $supplier->payment_terms) }}" placeholder="30 días, contado...">
        </div>
        <div class="form-group">
            <label class="form-label">Estado *</label>
            <select name="status" class="form-control" required>
                <option value="1" {{ old('status', $supplier->status) == 1 ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ old('status', $supplier->status) == 0 ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
</form>
