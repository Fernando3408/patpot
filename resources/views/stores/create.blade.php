<x-erp-layout title="Nueva sala - PatPot" subtitle="Registra una nueva sala asociada a un cliente en el sistema.">

    <div class="card">
        
        <div class="card__header">
            <h2 class="card__title">Nueva Sala</h2>
        </div>

        <div class="card__body">

            <form method="POST" action="{{ route('salas.store') }}">
                @csrf

                <div class="form-grid">

                    <div class="form-group">
                        <label class="form-label" for="customer_id">Cliente *:</label>
                        <select id="customer_id" name="customer_id" class="form-input" required>
                            <option value="">Selecciona un cliente</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                    {{ $customer->trade_name ?? $customer->business_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="code">Código de sala *:</label>
                        <input id="code" type="text" name="code" class="form-input" value="{{ old('code') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="name">Nombre:</label>
                        <input id="name" type="text" name="name" class="form-input" value="{{ old('name') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="city">Ciudad:</label>
                        <input id="city" type="text" name="city" class="form-input" value="{{ old('city') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="region">Región:</label>
                        <input id="region" type="text" name="region" class="form-input" value="{{ old('region') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="status">Estado *:</label>
                        <select id="status" name="status" class="form-input" required>
                            <option value="1" @selected(old('status', 1) == 1)>Activo</option>
                            <option value="0" @selected(old('status', 1) == 0)>Inactivo</option>
                        </select>
                    </div>

                </div>

                <div class="form-actions" style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <a href="{{ route('salas.index') }}" class="btn btn-outline-warning">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        Guardar sala
                    </button>
                </div>

            </form>

        </div>
    </div>

</x-erp-layout>