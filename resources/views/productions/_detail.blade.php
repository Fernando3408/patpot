    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem 1.5rem;">
        <div><strong>Número:</strong> {{ $production->number }}</div>
        <div><strong>Estado:</strong>
            @php
                $statusClass = match($production->status) {
                    'completed' => 'badge-success',
                    'in_progress' => 'badge-warning',
                    default => 'badge-info',
                };
                $statusLabel = match($production->status) {
                    'completed' => 'Completada',
                    'in_progress' => 'En proceso',
                    'planned' => 'Planificada',
                    default => $production->status,
                };
            @endphp
            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
        <div><strong>Producto:</strong> {{ $production->product->name ?? '—' }}</div>
        <div><strong>Fecha planificada:</strong> {{ $production->planned_on?->format('d/m/Y') ?? '—' }}</div>
        <div><strong>Fecha completada:</strong> {{ $production->completed_on?->format('d/m/Y') ?? '—' }}</div>
        <div><strong>Cajas planificadas:</strong> {{ number_format($production->planned_boxes, 0, ',', '.') }}</div>
        <div><strong>Cajas reales:</strong> {{ $production->actual_boxes !== null ? number_format($production->actual_boxes, 0, ',', '.') : '—' }}</div>
        <div style="grid-column:1/-1"><strong>Notas:</strong> {{ $production->notes ?? '—' }}</div>
    </div>
