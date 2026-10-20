<x-erp-layout title="Insumos" subtitle="Gestiona el inventario de materias primas con niveles de alerta y sugerencias de compra.">
    <div class="page-header">
        <div class="page-header-actions">
            <a href="{{ route('inputs.create') }}" class="btn btn-outline-primary btn-sm">+ Nuevo insumo</a>
        </div>
    </div>

    @if($inputs->count() > 0)
        <div class="table-container">
            <table class="data-table inputs-stock-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th class="text-right">Stock</th>
                        <th class="text-right">Stock mínimo</th>
                        <th>Unidad</th>
                        <th class="text-right">Costo sin IVA</th>
                        <th class="text-right">Costo con IVA</th>
                        <th>Nivel</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inputs as $input)
                        <tr data-type="{{ $input->type }}" data-input-id="{{ $input->id }}">
                            <td class="font-bold text-xs">{{ $input->code }}</td>
                            <td>
                                <strong>{{ $input->name }}</strong>
                                @if ($input->category)
                                    <br><span class="text-xs text-muted">{{ $input->category }}</span>
                                @endif
                            </td>
                            <td>
                                @if($input->type === 'service')
                                    <span class="badge badge-info">Servicio</span>
                                @else
                                    <span class="badge badge-secondary">Material</span>
                                @endif
                            </td>
                            @php
                                $formatQuantity = static fn ($value): string => (float) $value == (int) $value
                                    ? number_format((float) $value, 0, ',', '.')
                                    : rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
                            @endphp
                            <td class="text-right font-bold">{{ $formatQuantity($input->stock) }}</td>
                            <td class="text-right text-xs">{{ $formatQuantity($input->safety_stock) }}</td>
                            <td class="text-xs font-bold">{{ $input->unit }}</td>
                            <td class="text-right text-xs font-bold">${{ number_format($input->unit_cost, 0, ',', '.') }}</td>
                            <td class="text-right text-xs font-bold">${{ number_format($input->unit_cost_with_vat, 0, ',', '.') }}</td>
                            <td>
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
                                <details class="table-actions-menu">
                                    <summary>Acciones</summary>
                                    <div class="table-actions-menu__items">
                                    @if($input->type === 'material')
                                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="openAdjustModal({{ $input->id }}, '{{ addslashes($input->name) }}', {{ $input->stock }}, '{{ $input->unit }}')">Ajustar</button>
                                    @endif
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-modal" data-url="{{ route('inputs.edit', $input) }}" data-title="Editar: {{ $input->name }}">Editar</button>
                                    <button type="button" class="btn btn-outline-info btn-sm btn-detail-modal" data-url="{{ route('inputs.show', $input) }}" data-title="Detalle: {{ $input->name }}">Ver detalle</button>
                                    @if(auth()->user()->canManage())
                                        <form method="POST" action="{{ route('inputs.destroy', $input) }}" class="inline-form" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm btn-delete">Eliminar</button>
                                        </form>
                                    @endif
                                    </div>
                                </details>
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
        function useAverage(btn, inputId, value) {
            var formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('_method', 'PUT');
            formData.append('weekly_consumption', Math.round(value));
            fetch('/insumos/' + inputId, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(json) {
                if (json.errors) {
                    Swal.fire('Error', Object.values(json.errors).flat().join('\n'), 'error');
                } else {
                    Swal.fire({ icon: 'success', title: 'Consumo actualizado', timer: 1000, showConfirmButton: false });
                    setTimeout(function() { location.reload(); }, 800);
                }
            })
            .catch(function() {
                Swal.fire('Error', 'No se pudo actualizar.', 'error');
            });
        }

        function openAdjustModal(id, name, currentStock, unit) {
            var modal = document.getElementById('detailModal');
            var body = document.getElementById('detailModalBody');
            document.getElementById('detailModalTitle').textContent = 'Ajustar stock';

            var html = '<div class="mb-3"><strong>' + name + '</strong><br>Stock actual: <strong>' + Math.round(currentStock).toLocaleString('es-CL') + '</strong> ' + unit + '</div>';
            html += '<form id="adjustForm">';
            html += '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '">';
            html += '<div class="form-grid">';
            html += '<div class="form-group"><label class="form-label">Tipo de ajuste</label><select name="type" class="form-control"><option value="add">Sumar</option><option value="subtract">Restar</option><option value="set">Fijar stock real</option></select></div>';
            html += '<div class="form-group"><label class="form-label">Cantidad</label><input type="number" step="0.001" min="0" name="qty" class="form-control" required></div>';
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
                        closeDetailModal();
                        setTimeout(function() { location.reload(); }, 800);
                    }
                }).catch(function() {
                    Swal.fire('Error', 'No se pudo ajustar.', 'error');
                });
            };
        }
    </script>
</x-erp-layout>
