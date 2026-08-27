<x-erp-layout title="Nueva compra" subtitle="Las líneas vacías se ignoran; puedes agregar tantos insumos como necesites.">
    <div class="form-card">
        <form method="POST" action="/compras" enctype="multipart/form-data">
            @csrf

            {{-- Datos Generales de la Orden de Compra --}}
            <div class="form-grid mb-4">
                <div class="form-group">
                    <label class="form-label">Número OC</label>
                    <input name="number" class="form-control" value="{{ old('number') }}" placeholder="OC-0001" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Proveedor</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">Seleccione</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha emisión</label>
                    <input type="date" name="ordered_on" class="form-control" value="{{ old('ordered_on', today()->format('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Entrega estimada</label>
                    <input type="date" name="expected_on" class="form-control" value="{{ old('expected_on') }}">
                </div>
            </div>

            {{-- Tabla de Insumos (Líneas de Compra) --}}
            <div class="table-container mb-4">
                <table class="data-table" id="lines-table">
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th class="th-cantidad">Cantidad</th>
                            <th class="th-precio">Costo unitario</th>
                            <th class="th-action"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(range(0, 2) as $index)
                            <tr class="line-row">
                                <td>
                                    <select name="lines[{{ $index }}][input_id]" class="form-control">
                                        <option value="">-- Sin línea --</option>
                                        @foreach($inputs as $input)
                                            <option value="{{ $input->id }}" @selected(old("lines.$index.input_id") == $input->id)>
                                                {{ $input->code }} · {{ $input->name }} ({{ $input->unit }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.0001" min="0" name="lines[{{ $index }}][ordered_quantity]" class="form-control" value="{{ old("lines.$index.ordered_quantity") }}" placeholder="0">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="lines[{{ $index }}][unit_cost]" class="form-control" value="{{ old("lines.$index.unit_cost") }}" placeholder="$ 0">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-line" title="Eliminar línea">&times;</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-line-btn">+ Agregar línea</button>
            </div>

            {{-- Observaciones --}}
            <div class="form-group mb-4">
                <label class="form-label">Observaciones</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>

            {{-- Adjuntos --}}
            <div class="form-group mb-4">
                <label class="form-label">Archivos adjuntos</label>
                <div id="attachments-preview" style="margin-top:8px;"></div>
                <input type="file" name="files[]" id="attachments-input" multiple class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv" style="display:none;">
                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="document.getElementById('attachments-input').click();">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                    Adjuntar archivos
                </button>
                <p class="form-help mt-1">PDF, imágenes, Word, Excel. Máx 10 MB por archivo. Se guardan al guardar la compra.</p>
            </div>

            {{-- Botones de Acción --}}
            <div class="form-actions">
                <a href="/compras" class="btn btn-outline-warning">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    Guardar orden de compra
                </button>
            </div>
        </form>
    </div>

    @php
        $inputOptions = '';
        foreach($inputs as $input) {
            $inputOptions .= '<option value="'.e($input->id).'">'.e($input->code).' · '.e($input->name).' ('.e($input->unit).')</option>';
        }
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('lines-table');
            const tbody = table.querySelector('tbody');
            const addBtn = document.getElementById('add-line-btn');
            let lineIndex = {{ count(range(0, 2)) }};

            addBtn.addEventListener('click', function() {
                const row = document.createElement('tr');
                row.classList.add('line-row');
                row.innerHTML = `
                    <td>
                        <select name="lines[${lineIndex}][input_id]" class="form-control">
                            <option value="">-- Sin línea --</option>
                            {!! $inputOptions !!}
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.0001" min="0" name="lines[${lineIndex}][ordered_quantity]" class="form-control" value="" placeholder="0">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="lines[${lineIndex}][unit_cost]" class="form-control" value="" placeholder="$ 0">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-line" title="Eliminar línea">&times;</button>
                    </td>
                `;
                tbody.appendChild(row);
                lineIndex++;
            });

            tbody.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-remove-line')) {
                    const rows = tbody.querySelectorAll('.line-row');
                    if (rows.length > 1) {
                        e.target.closest('tr').remove();
                    }
                }
            });

            var fileInput = document.getElementById('attachments-input');
            var preview = document.getElementById('attachments-preview');
            var selectedFiles = [];

            fileInput.addEventListener('change', function() {
                var newFiles = Array.from(this.files);
                newFiles.forEach(function(f) {
                    if (f.size > 10 * 1024 * 1024) {
                        Swal.fire('Archivo muy grande', f.name + ' supera los 10 MB.', 'warning');
                        return;
                    }
                    selectedFiles.push(f);
                });
                renderPreview();
            });

            function renderPreview() {
                preview.innerHTML = '';
                selectedFiles.forEach(function(f, i) {
                    var item = document.createElement('div');
                    item.className = 'attachment-item';
                    var size = f.size < 1024 ? f.size + ' B' : f.size < 1048576 ? (f.size / 1024).toFixed(1) + ' KB' : (f.size / 1048576).toFixed(1) + ' MB';
                    item.innerHTML = '<span class="attachment-name">' + f.name + '</span><span class="attachment-size">' + size + '</span><button type="button" class="btn btn-danger btn-sm" onclick="removeAttachment(' + i + ')">&times;</button>';
                    preview.appendChild(item);
                });
            }

            function removeAttachment(i) {
                selectedFiles.splice(i, 1);
                renderPreview();
            }

            var form = fileInput.closest('form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(form);
                formData.delete('files[]');
                selectedFiles.forEach(function(f) { formData.append('files[]', f); });
                var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch('/compras', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then(function(r) { return r.json(); })
                .then(function(data) { window.location.href = '/compras'; })
                .catch(function() { form.submit(); });
            });
        });
    </script>
</x-erp-layout>