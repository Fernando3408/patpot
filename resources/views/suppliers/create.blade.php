<x-erp-layout title="Nuevo proveedor - PatPot" subtitle="Registra un nuevo proveedor en el sistema.">

    <div class="card">
        
        <div class="card__header">
            <h2 class="card__title">Nuevo Proveedor</h2>
        </div>

        <div class="card__body">

            <form method="POST" action="{{ route('proveedores.store') }}">
                @csrf

                <div class="form-grid">

                    <div class="form-group">
                        <label class="form-label" for="name">Nombre *:</label>
                        <input id="name" type="text" name="name" class="form-input" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="rut">RUT:</label>
                        <input id="rut" type="text" name="rut" class="form-input" value="{{ old('rut') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_name">Nombre de contacto:</label>
                        <input id="contact_name" type="text" name="contact_name" class="form-input" value="{{ old('contact_name') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email:</label>
                        <input id="email" type="email" name="email" class="form-input" value="{{ old('email') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Teléfono:</label>
                        <input id="phone" type="text" name="phone" class="form-input" value="{{ old('phone') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="lead_time_days">Lead time (días):</label>
                        <input id="lead_time_days" type="number" name="lead_time_days" class="form-input" value="{{ old('lead_time_days', 0) }}" min="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="payment_terms">Condiciones de pago:</label>
                        <input id="payment_terms" type="text" name="payment_terms" class="form-input" value="{{ old('payment_terms') }}" placeholder="30 días, contado...">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="status">Estado *:</label>
                        <select id="status" name="status" class="form-input" required>
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                </div>

                <div class="form-actions">
                    <a href="{{ route('proveedores.index') }}" class="btn btn-outline-warning">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        Guardar proveedor
                    </button>
                </div>

            </form>

        </div>
    </div>

</x-erp-layout>