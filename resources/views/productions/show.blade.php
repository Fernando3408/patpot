<x-erp-layout title="Detalle: {{ $production->number }}" subtitle="Información completa de la producción.">
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Producción</h2>
        </div>
        <div class="card__body">
            <div class="form-grid">
                <div><strong>Número:</strong> {{ $production->number }}</div>
                <div><strong>Producto:</strong> {{ $production->product->name ?? '—' }}</div>
                <div><strong>Fecha planificada:</strong> {{ $production->planned_on?->format('d/m/Y') ?? '—' }}</div>
                <div><strong>Cajas planificadas:</strong> {{ number_format($production->planned_boxes, 0, ',', '.') }}</div>
                <div><strong>Cajas reales:</strong> {{ $production->actual_boxes !== null ? number_format($production->actual_boxes, 0, ',', '.') : '—' }}</div>
                <div><strong>Fecha completada:</strong> {{ $production->completed_on?->format('d/m/Y') ?? '—' }}</div>
                <div>
                    <strong>Estado:</strong>
                    @php
                        $statusClass = match($production->status) {
                            'closed' => 'badge-success',
                            default => 'badge-info',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">
                        {{ $production->status === 'closed' ? 'Cerrada' : 'Planificada' }}
                    </span>
                </div>
                <div><strong>Notas:</strong> {{ $production->notes ?? '—' }}</div>
            </div>
        </div>
    </div>
</x-erp-layout>
