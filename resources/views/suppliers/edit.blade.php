@if(!request()->ajax())
<x-erp-layout title="Editar proveedor - PatPot" subtitle="Modifica los datos del proveedor en el sistema.">

    <div class="card">
        
        <div class="card__header">
            <h2 class="card__title">Editar Proveedor: {{ $supplier->name }}</h2>
        </div>

        <div class="card__body">

            <form method="POST" action="{{ route('proveedores.update', $supplier) }}">
                @csrf
                @method('PUT')

                <div class="form-grid">

                    <div class="form-group">
                        <label class="form-label" for="name">Nombre *:</label>
                        <input id="name" type="text" name="name" class="form-input" value="{{ old('name', $supplier->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="rut">RUT:</label>
                        <input id="rut" type="text" name="rut" class="form-input" value="{{ old('rut', $supplier->rut) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_name">Nombre de contacto:</label>
                        <input id="contact_name" type="text" name="contact_name" class="form-input" value="{{ old('contact_name', $supplier->contact_name) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Correo electrónico:</label>
                        <input id="email" type="email" name="email" class="form-input" value="{{ old('email', $supplier->email) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Teléfono:</label>
                        <input id="phone" type="text" name="phone" class="form-input" value="{{ old('phone', $supplier->phone) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="lead_time_days">Lead time (días):</label>
                        <input id="lead_time_days" type="number" min="0" name="lead_time_days" class="form-input" value="{{ old('lead_time_days', $supplier->lead_time_days) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="payment_terms">Condiciones de pago:</label>
                        <input id="payment_terms" type="text" name="payment_terms" class="form-input" value="{{ old('payment_terms', $supplier->payment_terms) }}" placeholder="30 días, contado...">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="status">Estado *:</label>
                        <select id="status" name="status" class="form-input" required>
                            <option value="1" {{ old('status', $supplier->status) == 1 ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('status', $supplier->status) == 0 ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                </div>

                <div class="form-actions" style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <a href="{{ route('proveedores.index') }}" class="btn btn-outline-warning">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        Guardar cambios
                    </button>
                </div>

            </form>

        </div>
    </div>

@if(!request()->ajax())
</x-erp-layout>