<x-erp-layout title="Insumos" subtitle="Gestiona el inventario de materias primas con niveles de alerta y sugerencias de compra.">
    <div class="page-header">
        <div class="page-header-actions">
            <a href="{{ route('inputs.create') }}" class="btn btn-outline-primary btn-sm">+ Nuevo insumo</a>
        </div>
    </div>

    @if($inputs->count() > 0)
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th class="text-right">Stock</th>
                        <th class="text-right">Seguridad</th>
                        <th>Unidad</th>
                        <th class="text-right">Costo</th>
                        <th class="text-center">Consumo sem.</th>
                        <th class="text-right">Reposición</th>
                        <th class="text-center">Cobertura</th>
                        <th>Nivel</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inputs as $input)
                        <tr data-update-url="{{ route('inputs.update', $input) }}">
                            <td data-field="code" class="font-bold text-xs">{{ $input->code }}</td>
                            <td data-field="name" data-value="{{ $input->name }}">
                                <strong>{{ $input->name }}</strong>
                                @if ($input->category)
                                    <br><span class="text-xs text-muted">{{ $input->category }}</span>
                                @endif
                            </td>
                            <td data-field="stock" data-cleanup="int" class="text-right font-bold">{{ number_format($input->stock, 0, ',', '.') }}</td>
                            <td data-field="safety_stock" data-cleanup="int" class="text-right text-xs">{{ number_format($input->safety_stock, 0, ',', '.') }}</td>
                            <td data-field="unit" class="text-xs font-bold">{{ $input->unit }}</td>
                            <td data-field="unit_cost" data-cleanup="int" class="text-right text-xs font-bold">${{ number_format($input->unit_cost, 0, ',', '.') }}</td>
                            <td data-field="weekly_consumption" data-cleanup="int" data-value="{{ (int) $input->weekly_consumption }}" data-calculated="true" class="text-center text-xs">
                                <div class="fw-600">{{ number_format($input->weekly_consumption, 0, ',', '.') }}</div>
                                @php $auto = $input->auto_weekly_consumption; @endphp
                                @if($auto > 0 && $auto != $input->weekly_consumption)
                                    <div class="text-muted" style="font-size:0.7rem;">Real: {{ number_format($auto, 0, ',', '.') }}</div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" style="font-size:0.65rem;padding:0.1rem 0.3rem;margin-top:2px;" onclick="useAverage(this, {{ $auto }})">Usar promedio</button>
                                @endif
                            </td>
                            <td data-calculated="true" class="text-right text-xs">{{ number_format($input->reorder_point, 0, ',', '.') }}</td>
                            <td data-calculated="true" data-readonly="true" class="text-xs text-center">
                                @if($input->coverage_days !== null)
                                    <div class="fw-600 mb-1">{{ $input->coverage_days }} días</div>
                                    @php
                                        $maxDays = 90;
                                        $pct = min(($input->coverage_days / $maxDays) * 100, 100);
                                        $color = $input->coverage_days <= 7 ? '#dc2626' : ($input->coverage_days <= 21 ? '#f59e0b' : '#16a34a');
                                    @endphp
                                    <div class="coverage-bar-bg">
                                        <div class="coverage-bar-fill" style="width:{{ $pct }}%;background:{{ $color }};"></div>
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td data-calculated="true" data-readonly="true">
                                @php $level = $input->inventory_level; @endphp
                                @if($level === 'ok')
                                    <span class="badge badge-success">Óptimo</span>
                                @elseif($level === 'atencion')
                                    <span class="badge badge-warning">Atencion</span>
                                @else
                                    <span class="badge badge-danger">Crítico</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="openAdjustModal({{ $input->id }}, '{{ addslashes($input->name) }}', {{ $input->stock }}, '{{ $input->unit }}')">Ajustar</button>
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-inline" onclick="enableInlineEdit(this.closest('tr'))">Editar</button>
                                    <button type="button" class="btn btn-outline-info btn-sm btn-detail-modal" data-url="{{ route('inputs.show', $input) }}" data-title="Detalle: {{ $input->name }}">Ver detalle</button>
                                    @if(auth()->user()->canManage())
                                        <form method="POST" action="{{ route('inputs.destroy', $input) }}" class="inline-form" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm btn-delete">Eliminar</button>
                                        </form>
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
                <p>No hay insumos registrados.</p>
            </div>
        </div>
    @endif

    <script>
        function updateInputRowCells(row, json) {
            var cells = row.querySelectorAll('td');
            var allCells = Array.from(cells);
            var reorderCell = allCells[7];
            if (reorderCell) reorderCell.textContent = json.reorder_point != null ? Math.round(json.reorder_point).toLocaleString('es-CL') : '—';
            var coverageCell = allCells[8];
            if (coverageCell && json.coverage_days != null) {
                var maxDays = 90;
                var pct = Math.min((json.coverage_days / maxDays) * 100, 100);
                var color = json.coverage_days <= 7 ? '#dc2626' : (json.coverage_days <= 21 ? '#f59e0b' : '#16a34a');
                coverageCell.innerHTML = '<div class="fw-600 mb-1">' + Math.round(json.coverage_days) + ' días</div><div class="coverage-bar-bg"><div class="coverage-bar-fill" style="width:' + pct + '%;background:' + color + ';"></div></div>';
            } else if (coverageCell) {
                coverageCell.innerHTML = '—';
            }
            var levelCell = allCells[9];
            if (levelCell && json.inventory_level) {
                var badges = { ok: '<span class="badge badge-success">Óptimo</span>', atencion: '<span class="badge badge-warning">Atencion</span>', critico: '<span class="badge badge-danger">Crítico</span>' };
                levelCell.innerHTML = badges[json.inventory_level] || '';
            }
        }

        function useAverage(btn, value) {
            var row = btn.closest('tr');
            var td = row.querySelector('td[data-field="weekly_consumption"]');
            if (!td) return;
            var url = row.dataset.updateUrl;
            var formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('_method', 'PUT');
            formData.append('weekly_consumption', Math.round(value));
            fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(json) {
                if (json.errors) {
                    Swal.fire('Error', Object.values(json.errors).flat().join('\n'), 'error');
                } else {
                    td.querySelector('.fw-600').textContent = Math.round(value).toLocaleString('es-CL');
                    btn.remove();
                    var realDiv = td.querySelector('.text-muted');
                    if (realDiv) realDiv.remove();
                    updateInputRowCells(row, json);
                    Swal.fire({ icon: 'success', title: 'Consumo actualizado', timer: 1000, showConfirmButton: false });
                }
            })
            .catch(function() {
                Swal.fire('Error', 'No se pudo actualizar.', 'error');
            });
        }

        window.onInlineEditSuccess = function(row, json) {
            updateInputRowCells(row, json);
            var cells = Array.from(row.querySelectorAll('td'));
            if (json.unit_cost != null) {
                cells[5].innerHTML = '$' + Math.round(json.unit_cost).toLocaleString('es-CL');
            }
            if (json.weekly_consumption != null) {
                cells[6].innerHTML = '<div class="fw-600">' + Math.round(json.weekly_consumption).toLocaleString('es-CL') + '</div>';
            }
        };

        function openAdjustModal(id, name, currentStock, unit) {
            var modal = document.getElementById('detailModal');
            var body = document.getElementById('detailModalBody');
            document.getElementById('detailModalTitle').textContent = 'Ajustar stock';

            var html = '<div class="mb-3"><strong>' + name + '</strong><br>Stock actual: <strong>' + Math.round(currentStock).toLocaleString('es-CL') + '</strong> ' + unit + '</div>';
            html += '<form id="adjustForm">';
            html += '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '">';
            html += '<div class="form-grid">';
            html += '<div class="form-group"><label class="form-label">Tipo de ajuste</label><select name="type" class="form-control"><option value="add">Sumar</option><option value="subtract">Restar</option><option value="set">Fijar stock real</option></select></div>';
            html += '<div class="form-group"><label class="form-label">Cantidad</label><input type="number" step="0.01" min="0" name="qty" class="form-control" required></div>';
            html += '<div class="form-group col-span-full"><label class="form-label">Motivo *</label><input type="text" name="reason" class="form-control" required placeholder="Ej: Conteo fisico, merma, correccion..."></div>';
            html += '</div>';
            html += '<div class="form-actions mt-4"><button type="button" class="btn btn-outline-warning" onclick="closeDetailModal()">Cancelar</button> <button type="submit" class="btn btn-primary">Confirmar ajuste</button></div>';
            html += '</form>';

            body.innerHTML = html;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            document.getElementById('adjustForm').onsubmit = function(e) {
                e.preventDefault();
                var fd = new FormData(this);
                fetch('/insumos/' + id + '/adjust', {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function(r) { return r.json(); })
                .then(function(json) {
                    if (json.errors) {
                        var msgs = Object.values(json.errors).flat().join('\n');
                        Swal.fire('Error', msgs, 'error');
                    } else {
                        Swal.fire('Ajustado', 'Stock actualizado a ' + Math.round(json.stock).toLocaleString('es-CL') + ' ' + unit, 'success');
                        setTimeout(function() { location.reload(); }, 800);
                    }
                }).catch(function() {
                    Swal.fire('Error', 'No se pudo ajustar.', 'error');
                });
            };
        }
    </script>
</x-erp-layout>
