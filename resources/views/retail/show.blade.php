<x-erp-layout title="Detalle: Retail" subtitle="Información completa del registro.">
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Retail</h2>
            <a href="{{ route('retail.index') }}" class="btn-outline-info btn-sm">← Volver</a>
        </div>
        <div class="card__body">
            <div class="form-grid">
                <div><strong>Sala:</strong> {{ $record->store?->code }} — {{ $record->store?->customer?->trade_name ?? $record->store?->customer?->business_name }}</div>
                <div><strong>Producto:</strong> {{ $record->product?->name }} ({{ $record->product?->sku }})</div>
                <div><strong>Catalogado:</strong> {{ $record->cataloged ? 'Sí' : 'No' }}</div>
                <div><strong>Stock unidades:</strong> {{ number_format($record->stock_units, 0, ',', '.') }}</div>
                <div><strong>Tránsito unidades:</strong> {{ number_format($record->transit_units, 0, ',', '.') }}</div>
                <div><strong>Venta semanal:</strong> {{ number_format($record->weekly_sales, 0, ',', '.') }}</div>
                <div><strong>Cobertura:</strong> {{ $record->coverage_weeks !== null ? number_format($record->coverage_weeks, 1, ',', '.') . ' semanas' : '—' }}</div>
                <div><strong>Quiebre:</strong>
                    @if($record->is_break)
                        <span class="badge alert-danger">QUIEBRE</span>
                    @else
                        <span class="badge badge-success">No</span>
                    @endif
                </div>
                <div><strong>Reposición sugerida:</strong> {{ number_format($record->suggested_replenishment_boxes, 0, ',', '.') }} cajas</div>
            </div>
        </div>
    </div>
</x-erp-layout>
