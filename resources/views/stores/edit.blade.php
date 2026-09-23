<form method="POST" action="{{ route('salas.update', $store) }}">
    @csrf
    @method('PUT')

    <h3 class="text-sm" style="margin-top:0;">Datos de la Sala</h3>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Cliente *</label>
            <select name="customer_id" class="form-control" required>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(old('customer_id', $store->customer_id) == $customer->id)>{{ $customer->trade_name ?? $customer->business_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Código de sala *</label>
            <input type="text" name="code" class="form-control" value="{{ old('code', $store->code) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $store->name) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Ciudad</label>
            <input type="text" name="city" class="form-control" value="{{ old('city', $store->city) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Región</label>
            <input type="text" name="region" class="form-control" value="{{ old('region', $store->region) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Estado *</label>
            <select name="status" class="form-control" required>
                <option value="1" @selected(old('status', $store->status) == 1)>Activo</option>
                <option value="0" @selected(old('status', $store->status) == 0)>Inactivo</option>
            </select>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
</form>
