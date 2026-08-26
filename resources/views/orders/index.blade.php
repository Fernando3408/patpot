<x-erp-layout title="Pedidos y despachos" subtitle="Gestiona pedidos por cliente y descuenta automáticamente el stock al despachar.">
    <div class="page-header">
        <div class="page-header-filters">
            <form method="GET" action="{{ route('pedidos.index') }}" class="search-form">
                <input type="date" name="from" class="form-control" value="{{ request('from') }}" placeholder="Desde">
                <input type="date" name="to" class="form-control" value="{{ request('to') }}" placeholder="Hasta">
                <select name="status" class="form-control">
                    <option value="">Todos los estados</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Parcial</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completado</option>
                </select>
                <button type="submit" class="btn btn-outline-success btn-sm">Filtrar</button>
                @if(request()->hasAny(['from', 'to', 'status']))
                    <a href="{{ route('pedidos.index') }}" class="btn btn-outline-warning btn-sm">Limpiar</a>
                @endif
            </form>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('pedidos.create') }}" class="btn btn-outline-primary btn-sm">＋ Nuevo pedido</a>
        </div>
    </div>

    @if($orders->isNotEmpty())
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Productos</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Margen</th>
                        <th>Estado</th>
                        <th class="th-progreso">Progreso</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr data-order-id="{{ $order->id }}" data-update-url="{{ route('pedidos.update', $order) }}">
                            <td>
                                <strong>{{ $order->number }}</strong>
                                <br>
                                <span class="text-xs text-muted">{{ $order->delivery_on?->format('d-m-Y') ?? 'Sin fecha de entrega' }}</span>
                            </td>
                            <td>
                                <strong>{{ $order->customer?->trade_name ?: $order->customer?->business_name ?? 'Cliente eliminado' }}</strong>
                                <br>
                                <span class="text-xs text-muted">{{ $order->store?->name ?: 'Sin sala específica' }}</span>
                            </td>
                            <td>
                                <div class="text-xs">
                                    @foreach($order->lines->take(5) as $line)
                                        <div>{{ $line->product?->name ?? '—' }} <span class="text-muted">×{{ number_format($line->boxes, 0, ',', '.') }} cajas</span></div>
                                    @endforeach
                                    @if($order->lines->count() > 5)
                                        <div class="text-muted">+{{ $order->lines->count() - 5 }} más</div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right">
                                @php
                                    $orderTotal = $order->lines->sum(fn($line) => $line->boxes * $line->price_box * (1 - ($line->discount_pct ?? 0) / 100));
                                    $orderCost = $order->lines->sum(fn($line) => $line->boxes * ($line->product?->cost_per_box ?? 0));
                                    $orderMargin = $orderTotal - $orderCost;
                                @endphp
                                <strong>${{ number_format($orderTotal, 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-right">
                                <span class="{{ $orderMargin >= 0 ? 'text-positive' : 'text-negative' }} fw-600">${{ number_format($orderMargin, 0, ',', '.') }}</span>
                                @if($orderTotal > 0)
                                    <span class="text-xs text-muted">{{ number_format($orderMargin / $orderTotal * 100, 1) }}%</span>
                                @endif
                            </td>
                            <td data-readonly="true">
                                @php
                                    $badgeClass = match($order->status) {
                                        'completed' => 'badge-success',
                                        'partial' => 'badge-info',
                                        default => 'badge-warning',
                                    };
                                    $statusLabel = match($order->status) {
                                        'completed' => 'Completado',
                                        'partial' => 'Parcial',
                                        default => 'Pendiente',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                @php
                                    $totalBoxes = $order->lines->sum(fn($line) => (float) $line->boxes);
                                    $totalDispatched = $order->lines->sum(fn($line) => (float) $line->dispatched_boxes);
                                    $pct = $totalBoxes > 0 ? round(($totalDispatched / $totalBoxes) * 100) : 0;
                                    $barClass = $pct >= 100 ? 'progress-bar-success' : ($pct > 0 ? 'progress-bar-warning' : 'progress-bar-info');
                                @endphp
                                <div class="progress-bar-container"><div class="progress-bar {{ $barClass }}" style="width: {{ $pct }}%;"></div></div>
                                <span class="text-xs text-muted">{{ number_format($totalDispatched, 0, ',', '.') }} / {{ number_format($totalBoxes, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="showInlineDetail(this)" data-title="Detalle: {{ $order->number }}">Ver detalle</button>
                                    <template>
                                        <div class="card">
                                            <div class="card__header"><h2 class="card__title">Pedido</h2></div>
                                            <div class="card__body">
                                                <div class="form-grid">
                                                    <div><strong>Número:</strong> {{ $order->number }}</div>
                                                    <div><strong>Cliente:</strong> {{ $order->customer?->trade_name ?: $order->customer?->business_name ?? '—' }}</div>
                                                    <div><strong>Sala:</strong> {{ $order->store?->name ?? '—' }}</div>
                                                    <div><strong>Fecha de orden:</strong> {{ $order->ordered_on?->format('d/m/Y') ?? '—' }}</div>
                                                    <div><strong>Fecha de entrega:</strong> {{ $order->delivery_on?->format('d/m/Y') ?? '—' }}</div>
                                                    <div><strong>Estado:</strong> <span class="badge {{ $order->status === 'completed' ? 'badge-success' : ($order->status === 'partial' ? 'badge-info' : 'badge-warning') }}">{{ $order->status === 'completed' ? 'Completado' : ($order->status === 'partial' ? 'Parcial' : 'Pendiente') }}</span></div>
                                                    <div><strong>Notas:</strong> {{ $order->notes ?? '—' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($order->lines->count())
                                        <div class="card mt-4">
                                            <div class="card__header"><h2 class="card__title">Líneas de pedido ({{ $order->lines->count() }} productos)</h2></div>
                                            <div class="card__body">
                                                <table class="data-table">
                                                    <thead><tr><th>Producto</th><th class="text-right">Cajas</th><th class="text-right">Precio/caja</th><th class="text-right">Descuento</th><th class="text-right">Subtotal</th><th class="text-right">Despachado</th><th class="text-center th-progreso">Progreso</th></tr></thead>
                                                    <tbody>
                                                        @php $grandTotal = 0; @endphp
                                                        @foreach($order->lines as $line)
                                                            @php
                                                                $boxes = (float) $line->boxes;
                                                                $dispatched = (float) $line->dispatched_boxes;
                                                                $pct = $boxes > 0 ? round(($dispatched / $boxes) * 100) : 0;
                                                                $barClass = $pct >= 100 ? 'progress-bar-success' : ($pct > 0 ? 'progress-bar-warning' : 'progress-bar-info');
                                                                $lineSubtotal = $line->boxes * $line->price_box * (1 - ($line->discount_pct ?? 0) / 100);
                                                                $grandTotal += $lineSubtotal;
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $line->product?->name ?? '—' }}</td>
                                                                <td class="text-right">{{ number_format($line->boxes, 0, ',', '.') }}</td>
                                                                <td class="text-right">${{ number_format($line->price_box, 0, ',', '.') }}</td>
                                                                <td class="text-right">{{ $line->discount_pct ?? 0 }}%</td>
                                                                <td class="text-right">${{ number_format($lineSubtotal, 0, ',', '.') }}</td>
                                                                <td class="text-right">{{ number_format($line->dispatched_boxes, 0, ',', '.') }}</td>
                                                                <td class="text-center">
                                                                    <div class="progress-bar-container"><div class="progress-bar {{ $barClass }}" style="width: {{ $pct }}%;"></div></div>
                                                                    <span class="text-xs text-muted">{{ number_format($dispatched, 0, ',', '.') }} / {{ number_format($boxes, 0, ',', '.') }}</span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        <tr class="row-total">
                                                            <td colspan="4" class="text-right">Total:</td>
                                                            <td class="text-right">${{ number_format($grandTotal, 0, ',', '.') }}</td>
                                                            <td colspan="2"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endif
                                        @if($order->shipments->count())
                                        <div class="card mt-4">
                                            <div class="card__header"><h2 class="card__title">Historial de despachos ({{ $order->shipments->count() }})</h2></div>
                                            <div class="card__body">
                                                @php $runningTotal = 0; @endphp
                                                <table class="data-table">
                                                    <thead><tr><th>Fecha</th><th>Producto</th><th class="text-right">Cajas</th><th class="text-right">Precio/caja</th><th class="text-right">Subtotal</th><th class="text-right">Acumulado</th></tr></thead>
                                                    <tbody>
                                                        @foreach($order->shipments->sortBy('shipped_on') as $shipment)
                                                            @foreach($shipment->lines as $sl)
                                                                @php
                                                                    $runningTotal += (float) $sl->boxes;
                                                                    $subtotal = $sl->boxes * $sl->price_box;
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $shipment->shipped_on->format('d/m/Y') }}</td>
                                                                    <td>{{ $sl->orderLine->product?->name ?? '—' }}</td>
                                                                    <td class="text-right">{{ (int) $sl->boxes }}</td>
                                                                    <td class="text-right">${{ number_format($sl->price_box, 0, ',', '.') }}</td>
                                                                    <td class="text-right">${{ number_format($subtotal, 0, ',', '.') }}</td>
                                                                    <td class="text-right font-bold">{{ (int) $runningTotal }}</td>
                                                                </tr>
                                                            @endforeach
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endif
                                    </template>
                                    @if(!$order->lines->contains(fn($line) => $line->dispatched_boxes > 0))
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-inline" onclick="enableInlineEdit(this.closest('tr'))">Editar</button>
                                    @endif
                                    @if(auth()->user()->canManage() && !$order->lines->contains(fn($line) => $line->dispatched_boxes > 0) && !in_array($order->status, ['completed', 'cancelled']))
                                        <form method="POST" action="{{ route('pedidos.destroy', $order) }}" class="inline-form" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm btn-delete">Eliminar</button>
                                        </form>
                                    @endif
                                    @if(!in_array($order->status, ['completed', 'cancelled']))
                                        <button type="button" class="btn btn-primary btn-sm" onclick='openDispatchModal({!! json_encode($order->lines->map(fn($line) => ["id" => $line->id, "name" => $line->product->name ?? "—", "stock" => (int)$line->product->stock_boxes, "boxes" => $line->boxes, "dispatched" => $line->dispatched_boxes])) !!}, "{{ route('orders.dispatch', $order) }}")'>Despachar</button>
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
                <p>No hay pedidos registrados.</p>
            </div>
        </div>
    @endif

    <script>
        function openDispatchModal(lines, url) {
            var modal = document.getElementById('detailModal');
            var body = document.getElementById('detailModalBody');
            document.getElementById('detailModalTitle').textContent = 'Despachar pedido';
            var match = url.match(/\/pedidos\/(\d+)\//);
            modal.dataset.orderId = match ? match[1] : '';

            var html = '<form method="POST" action="' + url + '">';
            html += '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '">';
            html += '<table class="data-table"><thead><tr><th>Producto</th><th class="text-right">Stock</th><th class="text-right">Pedida</th><th class="text-right">Ya despachado</th><th class="text-right th-cajas">Despachar ahora</th></tr></thead><tbody>';

            lines.forEach(function(line) {
                var pending = Math.min(line.boxes - line.dispatched, line.stock);
                html += '<tr>';
                html += '<td>' + line.name + '</td>';
                html += '<td class="text-right">' + line.stock + '</td>';
                html += '<td class="text-right">' + Math.round(line.boxes).toLocaleString('es-CL') + '</td>';
                html += '<td class="text-right">' + Math.round(line.dispatched).toLocaleString('es-CL') + '</td>';
                html += '<td><input type="number" step="1" min="0" max="' + pending + '" name="quantities[' + line.id + ']" class="form-control form-control-sm" value="0"></td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            html += '<div class="form-group mt-4"><label class="form-label">Fecha de despacho</label><input type="date" name="shipped_on" class="form-control input-date" value="' + new Date().toISOString().slice(0, 10) + '"></div>';
            html += '<div class="form-actions mt-4"><button type="button" class="btn btn-outline-warning" onclick="closeDetailModal()">Cancelar</button> <button type="button" class="btn btn-primary" onclick="submitDispatchForm(this, \'' + url + '\')">Confirmar despacho</button></div>';
            html += '</form>';

            body.innerHTML = html;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function numberFormat(n) {
            return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function updateOrderRow(json) {
            var orderId = document.getElementById('detailModal').dataset.orderId;
            if (!orderId) return;
            var row = document.querySelector('tr[data-order-id="' + orderId + '"]');
            if (!row) return;
            var allTds = row.querySelectorAll('td');
            var statusTd = allTds[allTds.length - 3];
            var progressTd = allTds[allTds.length - 2];
            var badgeClass = { completed: 'badge-success', partial: 'badge-info' }[json.status] || 'badge-warning';
            statusTd.innerHTML = '<span class="badge ' + badgeClass + '">' + json.statusLabel + '</span>';
            var barClass = json.pct >= 100 ? 'progress-bar-success' : (json.pct > 0 ? 'progress-bar-warning' : 'progress-bar-info');
            progressTd.innerHTML = '<div class="progress-bar-container"><div class="progress-bar ' + barClass + '" style="width: ' + json.pct + '%;"></div></div><span class="text-xs text-muted">' + json.totalDispatched.toLocaleString('es-CL') + ' / ' + json.totalBoxes.toLocaleString('es-CL') + '</span>';
            if (json.status === 'completed') {
                var actionsTd = allTds[allTds.length - 1];
                var dispatchBtn = actionsTd.querySelector('.btn-primary');
                if (dispatchBtn) dispatchBtn.remove();
                var editBtn = actionsTd.querySelector('.btn-edit-inline');
                if (editBtn) editBtn.remove();
                var deleteForm = actionsTd.querySelector('.inline-form');
                if (deleteForm) deleteForm.remove();
            }
        }

        function updateDispatchedInModal(form) {
            var inputs = form.querySelectorAll('input[name^="quantities["]');
            var modalBody = document.getElementById('detailModalBody');
            var modalRows = modalBody.querySelectorAll('table tbody tr');
            inputs.forEach(function(inp) {
                var match = inp.name.match(/quantities\[(\d+)\]/);
                if (!match) return;
                var lineId = match[1];
                var dispatchedNow = parseInt(inp.value) || 0;
                if (dispatchedNow <= 0) return;
                for (var j = 0; j < modalRows.length; j++) {
                    var mInputs = modalRows[j].querySelectorAll('input[name="quantities[' + lineId + ']"]');
                    if (mInputs.length > 0) {
                        var dispatchedCell = modalRows[j].querySelectorAll('td')[3];
                        var current = parseInt(dispatchedCell.textContent.replace(/\./g, '').replace(/,/g, '')) || 0;
                        dispatchedCell.textContent = (current + dispatchedNow).toLocaleString('es-CL');
                        break;
                    }
                }
            });
        }

        function rebuildOrderHistory(json, orderId) {
            if (!json.history || !json.history.length) return;
            var row = document.querySelector('tr[data-order-id="' + orderId + '"]');
            if (!row) return;
            var template = row.querySelector('template');
            if (!template) return;
            var content = template.content;
            var existingCard = content.querySelector('.card:last-child');
            if (existingCard && existingCard.querySelector('.card__title') && existingCard.querySelector('.card__title').textContent.indexOf('Historial') !== -1) {
                existingCard.remove();
            }
            var card = document.createElement('div');
            card.className = 'card mt-4';
            var html = '<div class="card__header"><h2 class="card__title">Historial de despachos (' + json.historyCount + ')</h2></div>';
            html += '<div class="card__body"><table class="data-table"><thead><tr><th>Fecha</th><th>Producto</th><th class="text-right">Cajas</th><th class="text-right">Precio/caja</th><th class="text-right">Subtotal</th><th class="text-right">Acumulado</th></tr></thead><tbody>';
            json.history.forEach(function(h) {
                html += '<tr><td>' + h.date + '</td><td>' + h.product + '</td><td class="text-right">' + h.boxes.toLocaleString('es-CL') + '</td><td class="text-right">' + h.price_box + '</td><td class="text-right">' + h.subtotal + '</td><td class="text-right font-bold">' + h.accumulated.toLocaleString('es-CL') + '</td></tr>';
            });
            html += '</tbody></table></div>';
            card.innerHTML = html;
            content.appendChild(card);
        }

        function submitDispatchForm(btn, url) {
            var form = btn.closest('form');
            var formData = new FormData(form);
            var orderId = document.getElementById('detailModal').dataset.orderId;
            btn.disabled = true;
            btn.textContent = 'Procesando...';

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(json) {
                if (json.errors) {
                    Swal.fire('Error', Object.values(json.errors).flat().join('\n'), 'error');
                    btn.disabled = false;
                    btn.textContent = 'Confirmar despacho';
                } else {
                    Swal.fire({ icon: 'success', title: 'Despacho registrado', timer: 1200, showConfirmButton: false });
                    updateOrderRow(json);
                    updateDispatchedInModal(form);
                    rebuildOrderHistory(json, orderId);
                    form.querySelectorAll('input').forEach(function(inp) { inp.disabled = true; });
                    btn.textContent = '✓ Despachado';
                    btn.className = 'btn btn-success';
                    var cancelBtn = form.querySelector('.btn-outline-warning');
                    if (cancelBtn) {
                        cancelBtn.textContent = 'Ver historial';
                        cancelBtn.className = 'btn btn-outline-info';
                        cancelBtn.onclick = function() {
                            closeDetailModal();
                            var row = document.querySelector('tr[data-order-id="' + orderId + '"]');
                            if (row) {
                                var detailBtn = row.querySelector('.btn-outline-info');
                                if (detailBtn) detailBtn.click();
                            }
                        };
                    }
                }
            })
            .catch(function() {
                Swal.fire('Error', 'No se pudo procesar.', 'error');
                btn.disabled = false;
                btn.textContent = 'Confirmar despacho';
            });
        }
    </script>
</x-erp-layout>
