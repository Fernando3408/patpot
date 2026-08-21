    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Compra</h2>
        </div>
        <div class="card__body">
            <div class="form-grid">
                <div><strong>ID:</strong> {{ $purchase->id ?? 'NULL' }}</div>
                <div><strong>Número:</strong> {{ $purchase->number ?? 'NULL' }}</div>
                <div><strong>Proveedor:</strong> {{ $purchase->supplier->name ?? '—' }}</div>
                <div><strong>Fecha de orden:</strong> {{ $purchase->ordered_on?->format('d/m/Y') ?? '—' }}</div>
                <div><strong>Fecha esperada:</strong> {{ $purchase->expected_on?->format('d/m/Y') ?? '—' }}</div>
                <div>
                    <strong>Estado:</strong>
                    @php
                        $statusClass = match($purchase->status) {
                            'received' => 'badge-success',
                            'partial' => 'badge-warning',
                            default => 'badge-info',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">
                        {{ $purchase->status === 'received' ? 'Recibida' : ($purchase->status === 'partial' ? 'Parcial' : 'En tránsito') }}
                    </span>
                </div>
                <div><strong>Notas:</strong> {{ $purchase->notes ?? '—' }}</div>
            </div>
        </div>
    </div>

    @if($purchase->lines->count())
    <div class="card mt-4">
        <div class="card__header">
            <h2 class="card__title">Líneas de compra ({{ $purchase->lines->count() }} insumos)</h2>
        </div>
        <div class="card__body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Insumo</th>
                        <th class="text-right">Cantidad pedida</th>
                        <th class="text-right">Costo unitario</th>
                        <th class="text-right">Recibido</th>
                        <th class="text-center" style="width:200px;">Progreso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->lines as $line)
                        @php
                            $ordered = (float) $line->ordered_quantity;
                            $received = (float) $line->received_quantity;
                            $pct = $ordered > 0 ? round(($received / $ordered) * 100) : 0;
                            $barClass = $pct >= 100 ? 'progress-bar-success' : ($pct > 0 ? 'progress-bar-warning' : 'progress-bar-info');
                        @endphp
                        <tr>
                            <td>{{ $line->input?->name ?? '—' }}</td>
                            <td class="text-right">{{ number_format($line->ordered_quantity, 0, ',', '.') }}</td>
                            <td class="text-right">${{ number_format($line->unit_cost, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($line->received_quantity, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <div class="progress-bar-container">
                                    <div class="progress-bar {{ $barClass }}" style="width: {{ $pct }}%;"></div>
                                </div>
                                <span class="text-xs text-muted">{{ number_format($received, 0, ',', '.') }} / {{ number_format($ordered, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
