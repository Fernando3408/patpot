<x-erp-layout title="Nuevo cliente" subtitle="Registra la información comercial y de contacto del cliente.">

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

                    <div class="form-group">
                        <label class="form-label" for="contact">Contacto:</label>
                        <input id="contact" type="text" name="contact" class="form-input" value="{{ old('contact') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Correo electrónico:</label>
                        <input id="email" type="email" name="email" class="form-input" value="{{ old('email') }}">
                    </div>

                    <div class="form-group"><label class="form-label">Contactos adicionales</label>@foreach(range(0, 2) as $contactIndex)<div class="form-grid mt-1"><input name="contacts[{{ $contactIndex }}][name]" class="form-input" placeholder="Nombre"><input name="contacts[{{ $contactIndex }}][phone]" class="form-input" placeholder="Teléfono"><input name="contacts[{{ $contactIndex }}][email]" type="email" class="form-input" placeholder="Correo"></div>@endforeach</div>

                    <div class="form-group">
                        <label class="form-label" for="payment_terms">Condición de pago:</label>
                        <select id="payment_terms" name="payment_terms" class="form-input">
                            <option value="">Seleccione una condición</option>
                            @foreach(['Contado','30 días','60 días','90 días'] as $term)
                                <option value="{{ $term }}" @selected(old('payment_terms') === $term)>{{ $term }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="form-actions-end">
                    <button type="submit" class="btn btn-primary">
                        Guardar Cliente
                    </button>
                </div>

            </form>

        </div>

    </div>

</x-erp-layout>
