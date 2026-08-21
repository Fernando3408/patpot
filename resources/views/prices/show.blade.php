<x-erp-layout title="Detalle: Precio" subtitle="Información completa del registro.">
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Precio por Cliente</h2>
            <a href="{{ route('precios.index') }}" class="btn-outline-info btn-sm">← Volver</a>
        </div>
        <div class="card__body">
            <div class="form-grid">
                <div><strong>Cliente:</strong> {{ $price->customer?->business_name ?? '—' }}</div>
                <div><strong>Producto:</strong> {{ $price->product?->name ?? '—' }}</div>
                <div><strong>Precio base:</strong> ${{ number_format($price->price_box, 0, ',', '.') }}</div>
                <div>
                    <strong>Precio oferta:</strong>
                    @if($price->offer_price)
                        <span style="color:#6a9c3b;">${{ number_format($price->offer_price, 0, ',', '.') }}</span>
                    @else
                        —
                    @endif
                </div>
                <div><strong>Vigencia hasta:</strong> {{ $price->offer_until?->format('d/m/Y') ?? '—' }}</div>
                <div><strong>Precio efectivo:</strong> <strong>${{ number_format($price->effective_price, 0, ',', '.') }}</strong></div>
            </div>
        </div>
    </div>
</x-erp-layout>
