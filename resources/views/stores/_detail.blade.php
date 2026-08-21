<div class="card">
    <div class="card__header">
        <h2 class="card__title">Sala / Local</h2>
    </div>
    <div class="card__body">
        <div class="form-grid">
            <div><strong>Código:</strong> {{ $store->code }}</div>
            <div><strong>Nombre:</strong> {{ $store->name }}</div>
            <div><strong>Ciudad:</strong> {{ $store->city ?? '—' }}</div>
            <div><strong>Región:</strong> {{ $store->region ?? '—' }}</div>
            <div><strong>Cliente:</strong> {{ $store->customer?->trade_name ?? $store->customer?->business_name ?? '—' }}</div>
            <div>
                <strong>Estado:</strong>
                <span class="badge {{ $store->status ? 'badge-success' : 'badge-danger' }}">
                    {{ $store->status ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
    </div>
</div>
