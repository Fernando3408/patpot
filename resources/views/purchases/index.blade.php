<x-erp-layout title="Compras y recepciones" subtitle="Controla órdenes de compra, material en tránsito y recepciones parciales.">
    <div class="page-header">
        <div class="page-header-filters">
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
        </div>
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
                        <th class="text-right th-cantidad">Cantidad</th>
                        <th>Estado</th>
                        <th class="th-progreso">Progreso</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchases as $purchase)
                        <tr data-purchase-id="{{ $purchase->id }}" @if($purchase->status !== 'received' && !$purchase->lines->contains(fn($line) => $line->received_quantity > 0)) data-update-url="{{ route('compras.update', $purchase) }}" @endif>
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
                            <td data-field="ordered_quantity" data-cleanup="int" class="text-right font-bold" data-value="{{ (int) $totalOrdered }}">{{ number_format($totalOrdered, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $badgeClass = match($purchase->status) {
                                        'received' => 'badge-success',
                                        'partial' => 'badge-warning',
                                        default => 'badge-info',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
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
                                                    <thead><tr><th>Insumo</th><th class="text-right">Pedida</th><th class="text-right">Costo</th><th class="text-right">Recibido</th><th class="text-center th-progreso">Progreso</th></tr></thead>
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
                                        @if($purchase->receptions->count())
                                        <div class="card mt-4">
                                            <div class="card__header"><h2 class="card__title">Historial de recepciones ({{ $purchase->receptions->count() }})</h2></div>
                                            <div class="card__body">
                                                @php $runningTotal = 0; @endphp
                                                <table class="data-table">
                                                    <thead><tr><th>Fecha</th><th>Insumo</th><th class="text-right">Recibido</th><th class="text-right">Costo</th><th class="text-right">Subtotal</th><th class="text-right">Acumulado</th></tr></thead>
                                                    <tbody>
                                                        @foreach($purchase->receptions->sortBy('received_on') as $reception)
                                                            @foreach($reception->lines as $rl)
                                                                @php
                                                                    $runningTotal += (float) $rl->quantity;
                                                                    $subtotal = $rl->quantity * $rl->unit_cost;
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $reception->received_on->format('d/m/Y') }}</td>
                                                                    <td>{{ $rl->purchaseLine->input?->name ?? '—' }}</td>
                                                                    <td class="text-right">{{ number_format($rl->quantity, 0, ',', '.') }}</td>
                                                                    <td class="text-right">${{ number_format($rl->unit_cost, 0, ',', '.') }}</td>
                                                                    <td class="text-right">${{ number_format($subtotal, 0, ',', '.') }}</td>
                                                                    <td class="text-right font-bold">{{ number_format($runningTotal, 0, ',', '.') }}</td>
                                                                </tr>
                                                            @endforeach
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endif
                                    </template>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openAttachmentModal('App\\Models\\Purchase', {{ $purchase->id }}, 'Adjuntos: {{ $purchase->number }}')" title="Adjuntos"><i data-lucide="paperclip" class="icon-sm"></i></button>
                                    @if($purchase->status !== 'received' && !$purchase->lines->contains(fn($line) => $line->received_quantity > 0))
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-inline" onclick="enableInlineEdit(this.closest('tr'))">Editar</button>
                                    @endif
                                    @if(auth()->user()->canManage() && $purchase->status !== 'received' && !$purchase->lines->contains(fn($line) => $line->received_quantity > 0))
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
            var match = url.match(/\/compras\/(\d+)\//);
            modal.dataset.purchaseId = match ? match[1] : '';

            var html = '<form method="POST" action="' + url + '">';
            html += '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '">';
            html += '<table class="data-table"><thead><tr><th>Insumo</th><th class="text-right">Pedida</th><th class="text-right">Ya recibido</th><th class="text-right col-input">Recibir ahora</th></tr></thead><tbody>';

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
            html += '<div class="form-group mt-4"><label class="form-label">Fecha de recepción</label><input type="date" name="received_on" class="form-control input-date" value="' + new Date().toISOString().slice(0, 10) + '" required></div>';
            html += '<div class="form-actions mt-4"><button type="button" class="btn btn-outline-warning" onclick="closeDetailModal()">Cancelar</button> <button type="button" class="btn btn-primary" onclick="submitReceiveForm(this, \'' + url + '\')">Confirmar recepción</button></div>';
            html += '</form>';

            body.innerHTML = html;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function numberFormat(n) {
            return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function updatePurchaseRow(json) {
            var purchaseId = document.getElementById('detailModal').dataset.purchaseId;
            if (!purchaseId) return;
            var row = document.querySelector('tr[data-purchase-id="' + purchaseId + '"]');
            if (!row) return;
            var allTds = row.querySelectorAll('td');
            var statusTd = allTds[allTds.length - 3];
            var progressTd = allTds[allTds.length - 2];
            var badgeClass = { received: 'badge-success', partial: 'badge-warning' }[json.status] || 'badge-info';
            statusTd.innerHTML = '<span class="badge ' + badgeClass + '">' + json.statusLabel + '</span>';
            var barClass = json.pct >= 100 ? 'progress-bar-success' : (json.pct > 0 ? 'progress-bar-warning' : 'progress-bar-info');
            progressTd.innerHTML = '<div class="progress-bar-container"><div class="progress-bar ' + barClass + '" style="width: ' + json.pct + '%;"></div></div><span class="text-xs text-muted">' + json.totalReceived.toLocaleString('es-CL') + ' / ' + json.totalOrdered.toLocaleString('es-CL') + '</span>';
            if (json.status === 'received') {
                var actionsTd = allTds[allTds.length - 1];
                var receiveBtn = actionsTd.querySelector('.btn-primary');
                if (receiveBtn) receiveBtn.remove();
                var editBtn = actionsTd.querySelector('.btn-edit-inline');
                if (editBtn) editBtn.remove();
                var deleteForm = actionsTd.querySelector('.inline-form');
                if (deleteForm) deleteForm.remove();
            }
        }

        function updateReceivedInModal(form) {
            var inputs = form.querySelectorAll('input[name^="received["]');
            var modalBody = document.getElementById('detailModalBody');
            var modalRows = modalBody.querySelectorAll('table tbody tr');
            inputs.forEach(function(inp) {
                var match = inp.name.match(/received\[(\d+)\]/);
                if (!match) return;
                var lineId = match[1];
                var receivedNow = parseInt(inp.value) || 0;
                if (receivedNow <= 0) return;
                for (var j = 0; j < modalRows.length; j++) {
                    var mInputs = modalRows[j].querySelectorAll('input[name="received[' + lineId + ']"]');
                    if (mInputs.length > 0) {
                        var receivedCell = modalRows[j].querySelectorAll('td')[2];
                        var current = parseInt(receivedCell.textContent.replace(/\./g, '').replace(/,/g, '')) || 0;
                        receivedCell.textContent = (current + receivedNow).toLocaleString('es-CL');
                        break;
                    }
                }
            });
        }

        function rebuildPurchaseHistory(json, purchaseId) {
            if (!json.history || !json.history.length) return;
            var row = document.querySelector('tr[data-purchase-id="' + purchaseId + '"]');
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
            var html = '<div class="card__header"><h2 class="card__title">Historial de recepciones (' + json.historyCount + ')</h2></div>';
            html += '<div class="card__body"><table class="data-table"><thead><tr><th>Fecha</th><th>Insumo</th><th class="text-right">Recibido</th><th class="text-right">Costo</th><th class="text-right">Subtotal</th><th class="text-right">Acumulado</th></tr></thead><tbody>';
            json.history.forEach(function(h) {
                html += '<tr><td>' + h.date + '</td><td>' + h.input + '</td><td class="text-right">' + h.quantity + '</td><td class="text-right">' + h.unit_cost + '</td><td class="text-right">' + h.subtotal + '</td><td class="text-right font-bold">' + h.accumulated + '</td></tr>';
            });
            html += '</tbody></table></div>';
            card.innerHTML = html;
            content.appendChild(card);
        }

        function submitReceiveForm(btn, url) {
            var form = btn.closest('form');
            var formData = new FormData(form);
            var purchaseId = document.getElementById('detailModal').dataset.purchaseId;
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
                    btn.textContent = 'Confirmar recepción';
                } else {
                    Swal.fire({ icon: 'success', title: 'Recepción registrada', timer: 1200, showConfirmButton: false });
                    updatePurchaseRow(json);
                    updateReceivedInModal(form);
                    rebuildPurchaseHistory(json, purchaseId);
                    form.querySelectorAll('input').forEach(function(inp) { inp.disabled = true; });
                    btn.textContent = '✓ Recibido';
                    btn.className = 'btn btn-success';
                    var cancelBtn = form.querySelector('.btn-outline-warning');
                    if (cancelBtn) {
                        cancelBtn.textContent = 'Ver historial';
                        cancelBtn.className = 'btn btn-outline-info';
                        cancelBtn.onclick = function() {
                            closeDetailModal();
                            var row = document.querySelector('tr[data-purchase-id="' + purchaseId + '"]');
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
                btn.textContent = 'Confirmar recepción';
            });
        }
    </script>
</x-erp-layout>
