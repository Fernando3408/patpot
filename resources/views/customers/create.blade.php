<x-erp-layout title="Crear Cliente - PatPot" subtitle="Registra un nuevo cliente en el sistema.">

    <div class="card">
        
        <!-- Cabecera -->
        <div class="card__header">
            <h1 class="card__title">Crear Nuevo Cliente</h1>
        </div>

        <div class="card__body">

            <!-- Formulario de Creación -->
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf

                <div class="form-grid">

                    <div class="form-group">
                        <label class="form-label" for="code">Código *:</label>
                        <input id="code" type="text" name="code" class="form-input" value="{{ old('code') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="business_name">Razón social *:</label>
                        <input id="business_name" type="text" name="business_name" class="form-input" value="{{ old('business_name') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="trade_name">Nombre de fantasía:</label>
                        <input id="trade_name" type="text" name="trade_name" class="form-input" value="{{ old('trade_name') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="rut">RUT:</label>
                        <input id="rut" type="text" name="rut" class="form-input" value="{{ old('rut') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="type">Tipo:</label>
                        <input id="type" type="text" name="type" class="form-input" value="{{ old('type') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="channel">Canal:</label>
                        <input id="channel" type="text" name="channel" class="form-input" value="{{ old('channel') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact">Contacto:</label>
                        <input id="contact" type="text" name="contact" class="form-input" value="{{ old('contact') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Correo electrónico:</label>
                        <input id="email" type="email" name="email" class="form-input" value="{{ old('email') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="payment_terms">Condición de pago:</label>
                        <input id="payment_terms" type="text" name="payment_terms" class="form-input" value="{{ old('payment_terms') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="discount">Descuento (%) *:</label>
                        <input id="discount" type="number" step="0.01" min="0" max="100" name="discount" class="form-input" value="{{ old('discount', 0) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="status">Estado *:</label>
                        <select id="status" name="status" class="form-input" required>
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                </div>

                <div style="margin-top: 1.5rem; text-align: right;">
                    <button type="submit" class="btn btn-primary">
                        Guardar Cliente
                    </button>
                </div>

            </form>

        </div>

    </div>

</x-erp-layout>