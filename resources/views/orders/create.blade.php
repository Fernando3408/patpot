<x-erp-layout title="Nuevo pedido" subtitle="Puedes cargar tantos productos como necesites. Si dejas el precio vacío, se aplica el precio vigente del cliente o el precio base.">
    
    <div class="form-card">
        <form method="POST" action="/pedidos" enctype="multipart/form-data">
            @csrf

            {{-- Sección: Datos Generales --}}
            <div class="form-grid mb-6">
                <div class="form-group">
                    <label class="form-label">Número pedido</label>
                    <input name="number" class="form-control" value="{{ old('number') }}" placeholder="PED-0001" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Cliente</label>
                    <select name="customer_id" class="form-control" required>
                        <option value="">Seleccione un cliente</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                {{ $customer->trade_name ?: $customer->business_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Sala</label>
                    <select name="store_id" class="form-control">
                        <option value="">Sin sala específica</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" @selected(old('store_id') == $store->id)>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha pedido</label>
                    <input type="date" name="ordered_on" class="form-control" value="{{ old('ordered_on', today()->format('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha entrega</label>
                    <input type="date" name="delivery_on" class="form-control" value="{{ old('delivery_on') }}" required>
                </div>
            </div>

            {{-- Sección: Líneas de Pedido (Productos) --}}
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Productos del pedido</h3>
                <div class="table-container">
                    <table class="data-table" id="lines-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="th-cajas">Cajas</th>
                                <th class="th-precio">Precio/caja</th>
                                <th class="th-descuento">Desc. %</th>
                                <th class="th-action"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(range(0, 2) as $index)
                                <tr class="line-row">
                                    <td>
                                        <select name="lines[{{ $index }}][product_id]" class="form-control">
                                            <option value="">Sin línea</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" @selected(old("lines.$index.product_id") == $product->id)>
                                                    {{ $product->name }} · base ${{ number_format($product->sale_price_box, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="1" min="0" name="lines[{{ $index }}][boxes]" class="form-control" value="{{ old("lines.$index.boxes") }}" placeholder="0">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="lines[{{ $index }}][price_box]" class="form-control" value="{{ old("lines.$index.price_box") }}" placeholder="Automático">
                                    </td>
                                    <td>
                                        <input type="number" step="1" min="0" max="100" name="lines[{{ $index }}][discount_pct]" class="form-control" value="{{ old("lines.$index.discount_pct") }}" placeholder="0">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm btn-remove-line" title="Eliminar línea">&times;</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-line-btn">+ Agregar línea</button>
            </div>

            {{-- Sección: Observaciones --}}
            <div class="form-group mb-4">
                <label class="form-label">Observaciones</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Notas adicionales del pedido...">{{ old('notes') }}</textarea>
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
                <p class="form-help mt-1">PDF, imágenes, Word, Excel. Máx 10 MB por archivo. Se guardan al guardar el pedido.</p>
            </div>

            {{-- Botones de Acción --}}
            <div class="form-actions">
                <a href="/pedidos" class="btn btn-outline-warning">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    Guardar pedido
                </button>
            </div>
        </form>
    </div>

    @php
        $productOptions = '';
        foreach($products as $product) {
            $productOptions .= '<option value="'.e($product->id).'">'.e($product->name).' · base $'.number_format($product->sale_price_box, 0, ',', '.').'</option>';
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
                        <select name="lines[${lineIndex}][product_id]" class="form-control">
                            <option value="">Sin línea</option>
                            {!! $productOptions !!}
                        </select>
                    </td>
                    <td>
                        <input type="number" step="1" min="0" name="lines[${lineIndex}][boxes]" class="form-control" value="" placeholder="0">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="lines[${lineIndex}][price_box]" class="form-control" value="" placeholder="Automático">
                    </td>
                    <td>
                        <input type="number" step="1" min="0" max="100" name="lines[${lineIndex}][discount_pct]" class="form-control" value="" placeholder="0">
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
                fetch('/pedidos', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then(function(r) { return r.json(); })
                .then(function(data) { window.location.href = '/pedidos'; })
                .catch(function() { form.submit(); });
            });
        });
    </script>
</x-erp-layout>