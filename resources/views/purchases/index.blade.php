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
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="openDetailModal('{{ route('purchases.show', $purchase) }}', 'Detalle: {{ $purchase->number }}')">Ver detalle</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openAttachmentModal('App\\Models\\Purchase', {{ $purchase->id }}, 'Adjuntos: {{ $purchase->number }}')" title="Adjuntos"><i data-lucide="paperclip" class="icon-sm"></i></button>
                                    @if(!$purchase->lines->contains(fn($line) => $line->received_quantity > 0))
                                    <a href="{{ route('compras.edit', $purchase) }}" class="btn btn-outline-success btn-sm">Editar</a>
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

        function updatePurchaseRow(json, purchaseId) {
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
                    closeDetailModal();
                    updatePurchaseRow(json, purchaseId);
                    rebuildPurchaseHistory(json, purchaseId);
                    Swal.fire({ icon: 'success', title: 'Recepción registrada', timer: 1500, showConfirmButton: false });
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
