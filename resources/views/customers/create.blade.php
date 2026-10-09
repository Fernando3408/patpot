<x-erp-layout title="Nuevo cliente" subtitle="Registra la información comercial y de contacto del cliente.">

    <div class="card customer-create-card">
        
        <!-- Cabecera -->
        <div class="card__header">
            <h1 class="card__title">Crear Nuevo Cliente</h1>
        </div>

        <div class="card__body">

            <!-- Formulario de Creación -->
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf

                <div class="customer-form-section"><h2>Identificación</h2><div class="customer-form-grid">

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
                        <label class="form-label" for="channel">Canal:</label>
                        <select id="channel" name="channel" class="form-input">
                            <option value="">Seleccione un canal</option>
                            @foreach(['Supermercado','Distribuidor','Tienda especializada','HORECA','Exportación','Venta directa'] as $channel)
                                <option value="{{ $channel }}" @selected(old('channel') === $channel)>{{ $channel }}</option>
                            @endforeach
                        </select>
                    </div>

                    </div></div>

                    <div class="customer-form-section"><h2>Contactos</h2><div class="customer-contact-primary"><div class="form-group"><label class="form-label" for="contact">Nombre de contacto</label><input id="contact" type="text" name="contact" class="form-input" value="{{ old('contact') }}"></div><div class="form-group"><label class="form-label" for="email">Correo electrónico</label><input id="email" type="email" name="email" class="form-input" value="{{ old('email') }}"></div></div>
                    <div class="form-group"><label class="form-label">Contactos adicionales</label><div id="customer-contacts"><div class="customer-contact-row"><input name="contacts[0][name]" class="form-input" placeholder="Nombre"><input name="contacts[0][phone]" class="form-input" placeholder="Teléfono"><input name="contacts[0][email]" type="email" class="form-input" placeholder="Correo"></div></div><button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addCustomerContact()">+ Agregar otro contacto</button></div>

                    </div>
                    <div class="customer-form-section"><h2>Primera sala</h2><p class="form-hint">Puedes crear la primera sala ahora o agregarla después.</p><div class="customer-form-grid"><div class="form-group"><label class="form-label" for="store_name">Nombre de la sala</label><input id="store_name" type="text" name="store[name]" class="form-input" placeholder="Ej: Sala Providencia"></div><div class="form-group"><label class="form-label" for="store_code">Código</label><input id="store_code" type="text" name="store[code]" class="form-input" placeholder="Ej: SALA-001"></div><div class="form-group"><label class="form-label" for="store_city">Ciudad</label><input id="store_city" type="text" name="store[city]" class="form-input"></div><div class="form-group"><label class="form-label" for="store_region">Región</label><input id="store_region" type="text" name="store[region]" class="form-input"></div></div></div>

                    <div class="customer-form-section customer-payment-section"><h2>Condiciones comerciales</h2><div class="form-group customer-payment-field">
                        <label class="form-label" for="payment_terms">Condición de pago:</label>
                        <select id="payment_terms" name="payment_terms" class="form-input">
                            <option value="">Seleccione una condición</option>
                            @foreach(['Contado','30 días','60 días','90 días'] as $term)
                                <option value="{{ $term }}" @selected(old('payment_terms') === $term)>{{ $term }}</option>
                            @endforeach
                        </select>
                    </div></div>

                <div class="form-actions-end">
                    <button type="submit" class="btn btn-primary">
                        Guardar Cliente
                    </button>
                </div>

            </form>

        </div>

    </div>

</x-erp-layout>
<script>let customerContactIndex = 1; function addCustomerContact() { const row = document.createElement('div'); row.className = 'form-grid mt-1'; row.innerHTML = '<input name="contacts[' + customerContactIndex + '][name]" class="form-input" placeholder="Nombre"><input name="contacts[' + customerContactIndex + '][phone]" class="form-input" placeholder="Teléfono"><input name="contacts[' + customerContactIndex + '][email]" type="email" class="form-input" placeholder="Correo">'; document.getElementById('customer-contacts').appendChild(row); customerContactIndex++; }</script>
