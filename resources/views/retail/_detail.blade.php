<div class="card">
    <div class="card__header">
        <h2 class="card__title">Retail</h2>
    </div>
    <div class="card__body">
        <div class="form-grid">
            <div><strong>Sala:</strong> {{ $retail->store?->code }} — {{ $retail->store?->customer?->trade_name ?? $retail->store?->customer?->business_name }}</div>
            <div><strong>Producto:</strong> {{ $retail->product?->name }} ({{ $retail->product?->sku }})</div>
            <div><strong>Catalogado:</strong> {{ $retail->cataloged ? 'Sí' : 'No' }}</div>
            <div><strong>Stock unidades:</strong> {{ number_format($retail->stock_units, 0, ',', '.') }}</div>
            <div><strong>Tránsito unidades:</strong> {{ number_format($retail->transit_units, 0, ',', '.') }}</div>
            <div><strong>Venta semanal:</strong> {{ number_format($retail->weekly_sales, 0, ',', '.') }}</div>
            <div><strong>Cobertura:</strong> {{ $retail->coverage_weeks !== null ? number_format($retail->coverage_weeks, 1, ',', '.') . ' semanas' : '—' }}</div>
            <div><strong>Quiebre:</strong>
                @if($retail->is_break)
                    <span class="badge alert-danger">QUIEBRE</span>
                @else
                    <span class="badge badge-success">No</span>
                @endif
            </div>
            <div><strong>Reposición sugerida:</strong> {{ number_format($retail->suggested_replenishment_boxes, 0, ',', '.') }} cajas</div>
        </div>
    </div>
</div>
