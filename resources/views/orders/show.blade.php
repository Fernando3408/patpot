<x-erp-layout title="Detalle: {{ $order->number }}" subtitle="Información completa del pedido.">
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Pedido</h2>
        </div>
        <div class="card__body">
            <div class="form-grid">
                <div><strong>Número:</strong> {{ $order->number }}</div>
                <div><strong>Cliente:</strong> {{ $order->customer?->trade_name ?: $order->customer?->business_name ?? '—' }}</div>
                <div><strong>Sala:</strong> {{ $order->store?->name ?? '—' }}</div>
                <div><strong>Fecha de orden:</strong> {{ $order->ordered_on?->format('d/m/Y') ?? '—' }}</div>
                <div><strong>Fecha de entrega:</strong> {{ $order->delivery_on?->format('d/m/Y') ?? '—' }}</div>
                <div>
                    <strong>Estado:</strong>
                    @php
                        $badgeClass = match($order->status) {
                            'completed' => 'badge-success',
                            'partial' => 'badge-info',
                            default => 'badge-warning',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">
                        {{ $order->status === 'completed' ? 'Completado' : ($order->status === 'partial' ? 'Parcial' : 'Pendiente') }}
                    </span>
                </div>
                <div><strong>Notas:</strong> {{ $order->notes ?? '—' }}</div>
            </div>
        </div>
    </div>

    @if($order->lines->count())
    <div class="card mt-4">
        <div class="card__header">
            <h2 class="card__title">Líneas de pedido ({{ $order->lines->count() }} productos)</h2>
        </div>
        <div class="card__body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-right">Cajas</th>
                        <th class="text-right">Precio/caja</th>
                        <th class="text-right">Descuento</th>
                        <th class="text-right">Despachado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->lines as $line)
                        <tr>
                            <td>{{ $line->product?->name ?? '—' }}</td>
                            <td class="text-right">{{ number_format($line->boxes, 0, ',', '.') }}</td>
                            <td class="text-right">${{ number_format($line->price_box, 0, ',', '.') }}</td>
                            <td class="text-right">{{ $line->discount_pct ?? 0 }}%</td>
                            <td class="text-right">{{ number_format($line->dispatched_boxes, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-erp-layout>
