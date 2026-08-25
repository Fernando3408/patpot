<div class="card">
    <div class="card__header">
        <h2 class="card__title">Cliente</h2>
    </div>
    <div class="card__body">
        <div class="form-grid">
            <div><strong>Código:</strong> {{ $customer->code }}</div>
            <div><strong>Razón social:</strong> {{ $customer->business_name }}</div>
            <div><strong>Nombre comercial:</strong> {{ $customer->trade_name ?? '—' }}</div>
            <div><strong>RUT:</strong> {{ $customer->rut ?? '—' }}</div>
            <div><strong>Tipo:</strong> {{ $customer->type ?? '—' }}</div>
            <div><strong>Canal:</strong> {{ $customer->channel ?? '—' }}</div>
            <div><strong>Contacto:</strong> {{ $customer->contact ?? '—' }}</div>
            <div><strong>Email:</strong> {{ $customer->email ?? '—' }}</div>
            <div><strong>Condiciones de pago:</strong> {{ $customer->payment_terms ?? '—' }}</div>
            <div><strong>Descuento:</strong> {{ $customer->discount }}%</div>
            <div>
                <strong>Estado:</strong>
                <span class="badge {{ $customer->status ? 'badge-success' : 'badge-danger' }}">
                    {{ $customer->status ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
    </div>
</div>

@if($customer->stores->count())
<div class="card mt-4">
    <div class="card__header">
        <h2 class="card__title">Salas ({{ $customer->stores->count() }})</h2>
    </div>
    <div class="card__body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Ciudad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customer->stores as $store)
                    <tr>
                        <td>{{ $store->name }}</td>
                        <td class="text-xs">{{ $store->code }}</td>
                        <td>{{ $store->city ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($customer->prices->count())
<div class="card mt-4">
    <div class="card__header">
        <h2 class="card__title">Precios ({{ $customer->prices->count() }})</h2>
    </div>
    <div class="card__body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-right">Precio caja</th>
                    <th class="text-right">Precio oferta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customer->prices as $price)
                    <tr>
                        <td>{{ $price->product?->name ?? '—' }}</td>
                        <td class="text-right">${{ number_format($price->price_box, 0, ',', '.') }}</td>
                        <td class="text-right">
                            @if($price->offer_price)
                                <span class="text-price">${{ number_format($price->offer_price, 0, ',', '.') }}</span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
