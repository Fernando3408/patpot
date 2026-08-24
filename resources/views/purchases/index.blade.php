<x-erp-layout title="Compras y recepciones" subtitle="Controla órdenes de compra, material en tránsito y recepciones parciales.">
    <div class="page-header">
        <form method="GET" action="{{ route('compras.index') }}" class="search-form">
            <input type="date" name="from" class="form-control" value="{{ request('from') }}" placeholder="Desde">
            <input type="date" name="to" class="form-control" value="{{ request('to') }}" placeholder="Hasta">
            <select name="status" class="form-control">
                <option value="">Todos los estados</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En tránsito</option>
                <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Parcial</option>
                <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Recibida</option>
            </select>
            <button type="submit" class="btn btn-outline-success btn-sm">Filtrar</button>
            @if(request()->hasAny(['from', 'to', 'status']))
                <a href="{{ route('compras.index') }}" class="btn btn-outline-warning btn-sm">Limpiar</a>
            @endif
        </form>
        <div class="page-header-actions">
            <a href="{{ route('compras.create') }}" class="btn btn-outline-primary btn-sm">+ Nueva compra</a>
        </div>
    </div>

    @if($purchases->count() > 0)
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Proveedor</th>
                        <th>Insumos</th>
                        <th class="text-right" style="width:120px;">Cantidad</th>
                        <th>Estado</th>
                        <th style="width:180px;">Progreso</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchases as $purchase)
                        <tr data-update-url="{{ route('compras.update', $purchase) }}">
                            <td>
                                <div class="font-bold">{{ $purchase->number }}</div>
                                <div class="text-xs text-muted">
                                    {{ $purchase->ordered_on->format('d-m-Y') }} · entrega {{ $purchase->expected_on?->format('d-m-Y') ?? '—' }}
                                </div>
                            </td>
                            <td>{{ $purchase->supplier->name }}</td>
                            <td>
                                <div class="text-xs">
                                    @foreach($purchase->lines->take(5) as $line)
                                        <div>{{ $line->input?->name ?? '—' }}</div>
                                    @endforeach
                                    @if($purchase->lines->count() > 5)
                                        <div class="text-muted">+{{ $purchase->lines->count() - 5 }} más</div>
                                    @endif
                                </div>
                            </td>
                            @php $totalOrdered = $purchase->lines->sum(fn($line) => (float) $line->ordered_quantity); @endphp
                            <td data-field="ordered_quantity" data-cleanup="int" class="text-right font-bold" data-value="{{ $totalOrdered }}">{{ number_format($totalOrdered, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $statusClass = match($purchase->status) {
                                        'received' => 'text-success',
                                        'partial' => 'text-warning',
                                        default => 'text-info',
                                    };
                                @endphp
                                <span class="font-bold {{ $statusClass }}">
                                    {{ $purchase->status === 'received' ? 'Recibida' : ($purchase->status === 'partial' ? 'Parcial' : 'En tránsito') }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $totalOrdered = $purchase->lines->sum(fn($line) => (float) $line->ordered_quantity);
                                    $totalReceived = $purchase->lines->sum(fn($line) => (float) $line->received_quantity);
                                    $pct = $totalOrdered > 0 ? round(($totalReceived / $totalOrdered) * 100) : 0;
                                    $barClass = $pct >= 100 ? 'progress-bar-success' : ($pct > 0 ? 'progress-bar-warning' : 'progress-bar-info');
                                @endphp
                                <div class="progress-bar-container"><div class="progress-bar {{ $barClass }}" style="width: {{ $pct }}%;"></div></div>
                                <span class="text-xs text-muted">{{ number_format($totalReceived, 0, ',', '.') }} / {{ number_format($totalOrdered, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="showInlineDetail(this)" data-title="Detalle: {{ $purchase->number }}">Ver detalle</button>
                                    <template>
                                        <div class="card">
                                            <div class="card__header"><h2 class="card__title">Compra</h2></div>
                                            <div class="card__body">
                                                <div class="form-grid">
                                                    <div><strong>Número:</strong> {{ $purchase->number }}</div>
                                                    <div><strong>Proveedor:</strong> {{ $purchase->supplier->name ?? '—' }}</div>
                                                    <div><strong>Fecha de orden:</strong> {{ $purchase->ordered_on?->format('d/m/Y') ?? '—' }}</div>
                                                    <div><strong>Fecha esperada:</strong> {{ $purchase->expected_on?->format('d/m/Y') ?? '—' }}</div>
                                                    <div><strong>Estado:</strong> <span class="badge {{ $purchase->status === 'received' ? 'badge-success' : ($purchase->status === 'partial' ? 'badge-warning' : 'badge-info') }}">{{ $purchase->status === 'received' ? 'Recibida' : ($purchase->status === 'partial' ? 'Parcial' : 'En tránsito') }}</span></div>
                                                    <div><strong>Notas:</strong> {{ $purchase->notes ?? '—' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($purchase->lines->count())
                                        <div class="card mt-4">
                                            <div class="card__header"><h2 class="card__title">Líneas ({{ $purchase->lines->count() }} insumos)</h2></div>
                                            <div class="card__body">
                                                <table class="data-table">
                                                    <thead><tr><th>Insumo</th><th class="text-right">Pedida</th><th class="text-right">Costo</th><th class="text-right">Recibido</th><th class="text-center" style="width:200px;">Progreso</th></tr></thead>
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
                                                                    <div class="progress-bar-container"><div class="progress-bar {{ $barClass }}" style="width: {{ $pct }}%;"></div></div>
                                                                    <span class="text-xs text-muted">{{ number_format($received, 0, ',', '.') }} / {{ number_format($ordered, 0, ',', '.') }}</span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endif
                                    </template>
                                    @if(!$purchase->lines->contains(fn($line) => $line->received_quantity > 0) && $purchase->lines->count() === 1)
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-inline" onclick="enableInlineEdit(this.closest('tr'))">Editar</button>
                                    @endif
                                    @if(auth()->user()->canManage())
                                        <form method="POST" action="{{ route('compras.destroy', $purchase) }}" class="inline-form" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm btn-delete">Eliminar</button>
                                        </form>
                                    @endif
                                    @if($purchase->status !== 'received')
                                        <button type="button" class="btn btn-primary btn-sm" onclick='openReceiveModal({!! json_encode($purchase->lines->map(fn($line) => ["id" => $line->id, "name" => $line->input->name ?? "—", "ordered" => $line->ordered_quantity, "received" => $line->received_quantity])) !!}, "{{ route('purchases.receive', $purchase) }}")'>Recibir</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @else
        <div class="table-container">
            <div class="data-table-empty">
                <p>No hay compras registradas.</p>
            </div>
        </div>
    @endif

    <script>
        function openReceiveModal(lines, url) {
            var modal = document.getElementById('detailModal');
            var body = document.getElementById('detailModalBody');
            document.getElementById('detailModalTitle').textContent = 'Recibir mercadería';

            var html = '<form method="POST" action="' + url + '">';
            html += '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '">';
            html += '<table class="data-table"><thead><tr><th>Insumo</th><th class="text-right">Pedida</th><th class="text-right">Ya recibido</th><th style="width:150px;">Recibir ahora</th></tr></thead><tbody>';

            lines.forEach(function(line) {
                var pending = Math.round(line.ordered) - Math.round(line.received);
                html += '<tr>';
                html += '<td>' + line.name + '</td>';
                html += '<td class="text-right">' + Math.round(line.ordered).toLocaleString('es-CL') + '</td>';
                html += '<td class="text-right">' + Math.round(line.received).toLocaleString('es-CL') + '</td>';
                html += '<td><input type="number" step="1" min="0" max="' + pending + '" name="received[' + line.id + ']" class="form-control form-control-sm" value="0"></td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            html += '<div class="form-actions mt-4"><button type="button" class="btn btn-outline-warning" onclick="closeDetailModal()">Cancelar</button> <button type="submit" class="btn btn-primary">Confirmar recepción</button></div>';
            html += '</form>';

            body.innerHTML = html;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function numberFormat(n) {
            return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    </script>
</x-erp-layout>
