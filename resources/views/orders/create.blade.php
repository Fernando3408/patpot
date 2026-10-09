<x-erp-layout title="Nuevo pedido" subtitle="Puedes cargar tantos productos como necesites. Si dejas el precio vacío, se aplica el precio vigente del cliente.">
    
    <div class="form-card">
        <form method="POST" action="/pedidos" enctype="multipart/form-data">
            @csrf

            {{-- Sección: Datos Generales --}}
            <div class="form-grid order-header-grid mb-6">
                <div class="form-group order-system-number">
                    <label class="form-label">Número pedido</label>
                    <input name="number" class="form-control" value="{{ old('number') }}" placeholder="PED-0001" required>
                </div>

                <div class="form-group order-customer-field">
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
                    <label class="form-label">Orden de compra del cliente (opcional)</label>
                    <input name="customer_order_number" class="form-control" value="{{ old('customer_order_number') }}" placeholder="OC del cliente">
                </div>

                <div class="form-group">
                    <label class="form-label">Sala</label>
                    <select name="store_id" class="form-control">
                        <option value="">Sin sala específica</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" data-customer-id="{{ $store->customer_id }}" @selected(old('store_id') == $store->id)>
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
                                <th>Costo/caja</th>
                                <th>Subtotal</th>
                                <th class="th-action"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(range(0, 0) as $index)
                                <tr class="line-row">
                                    <td>
                                        <select name="lines[{{ $index }}][product_id]" class="form-control">
                                            <option value="">Seleccionar producto</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-cost="{{ $product->cost_per_box }}" @selected(old("lines.$index.product_id") == $product->id)>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="1" min="0" name="lines[{{ $index }}][boxes]" class="form-control" value="{{ old("lines.$index.boxes") }}" placeholder="Cajas">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="lines[{{ $index }}][price_box]" class="form-control" value="{{ old("lines.$index.price_box") }}" placeholder="Automático">
                                    </td>
                                    <td class="line-cost">$0</td><td class="line-subtotal">$0</td>
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

            <div class="card p-4 mb-6" id="order-consumption-panel" style="display:none;">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Consumo estimado del pedido</h3>
                <div id="order-consumption-content" class="text-sm text-muted">Selecciona productos y cantidades para ver el impacto en los insumos.</div>
            </div>

            <div class="card p-4 mb-6 order-summary" id="order-summary">
                <div><span>Venta</span><strong id="order-sale-total">$0</strong></div><div><span>Costo productos</span><strong id="order-cost-total">$0</strong></div><div><span>Gastos asociados</span><strong id="order-expense-total">$0</strong></div><div><span>Utilidad estimada</span><strong id="order-profit-total">$0</strong></div><div><span>Margen</span><strong id="order-margin-total">0%</strong></div>
            </div>

            <div class="card p-4 mb-6">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Gastos asociados al pedido</h3>
                @foreach(range(0, 2) as $expenseIndex)
                    <div class="form-grid mb-2">
                        <select name="expenses[{{ $expenseIndex }}][concept]" class="form-control"><option value="">Concepto</option>@foreach($expenseConcepts as $concept)<option value="{{ $concept }}">{{ $concept }}</option>@endforeach<option value="Otro">Otro</option></select>
                        <select name="expenses[{{ $expenseIndex }}][kind]" class="form-control"><option value="fixed">Monto fijo</option><option value="percent">Porcentaje</option></select>
                        <input type="number" step="0.01" min="0" name="expenses[{{ $expenseIndex }}][value]" class="form-control" placeholder="Monto o porcentaje">
                        <input type="text" name="expenses[{{ $expenseIndex }}][notes]" class="form-control" placeholder="Detalle opcional">
                    </div>
                @endforeach
            </div>

            <details class="order-secondary-details mb-4"><summary>Observaciones y archivos adjuntos</summary>
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

            </details>

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
            $productOptions .= '<option value="'.e($product->id).'" data-cost="'.e($product->cost_per_box).'">'.e($product->name).'</option>';
        }
        $productRecipeData = $products->mapWithKeys(function ($product) {
            return [$product->id => $product->recipes->map(function ($recipe) {
                return ['input_id' => $recipe->input_id, 'name' => $recipe->input?->name, 'unit' => $recipe->input?->unit, 'stock' => (float) $recipe->input?->stock, 'safety' => (float) $recipe->input?->safety_stock, 'qty' => (float) $recipe->qty_per_box];
            })->values()->toArray()];
        })->toArray();
    @endphp
    <script>
        var productRecipeData = @json($productRecipeData);
        var negotiatedPrices = @json($prices->mapWithKeys(fn ($price) => ["{$price->customer_id}-{$price->product_id}" => (float) $price->price_box]));

        function fillNegotiatedPrices() {
            var customerId = document.querySelector('[name="customer_id"]')?.value;
            document.querySelectorAll('#lines-table .line-row').forEach(function(row) {
                var productId = row.querySelector('[name*="[product_id]"]')?.value;
                var priceInput = row.querySelector('[name*="[price_box]"]');
                var price = negotiatedPrices[customerId + '-' + productId];
                if (price && priceInput && !priceInput.value) priceInput.value = price;
            });
            updateOrderSummary();
        }

        function updateOrderSummary() {
            var sale = 0, cost = 0, expenses = 0;
            document.querySelectorAll('#lines-table .line-row').forEach(function(row) {
                var option = row.querySelector('select')?.selectedOptions[0];
                var boxes = parseFloat(row.querySelector('[name*="[boxes]"]')?.value || 0);
                var price = parseFloat(row.querySelector('[name*="[price_box]"]')?.value || 0);
                var lineCost = parseFloat(option?.dataset.cost || 0);
                sale += boxes * price; cost += boxes * lineCost;
                row.querySelector('.line-cost').textContent = '$' + Math.round(lineCost).toLocaleString('es-CL');
                row.querySelector('.line-subtotal').textContent = '$' + Math.round(boxes * price).toLocaleString('es-CL');
            });
            document.querySelectorAll('[name*="[value]"]').forEach(function(input) { var value = parseFloat(input.value || 0); var kind = input.closest('.form-grid')?.querySelector('select')?.value; expenses += kind === 'percent' ? sale * value / 100 : value; });
            var profit = sale - cost - expenses;
            document.getElementById('order-sale-total').textContent = '$' + Math.round(sale).toLocaleString('es-CL');
            document.getElementById('order-cost-total').textContent = '$' + Math.round(cost).toLocaleString('es-CL');
            document.getElementById('order-expense-total').textContent = '$' + Math.round(expenses).toLocaleString('es-CL');
            document.getElementById('order-profit-total').textContent = '$' + Math.round(profit).toLocaleString('es-CL');
            document.getElementById('order-margin-total').textContent = (sale ? (profit / sale * 100).toFixed(1) : '0') + '%';
        }

        function updateConsumptionPanel() {
            var totals = {};
            document.querySelectorAll('#lines-table .line-row').forEach(function(row) {
                var productId = row.querySelector('[name*="[product_id]"]')?.value;
                var boxes = parseFloat(row.querySelector('[name*="[boxes]"]')?.value || 0);
                if (!productId || boxes <= 0 || !productRecipeData[productId]) return;
                productRecipeData[productId].forEach(function(recipe) {
                    if (!totals[recipe.input_id]) totals[recipe.input_id] = { name: recipe.name, unit: recipe.unit, stock: recipe.stock, safety: recipe.safety, quantity: 0 };
                    totals[recipe.input_id].quantity += boxes * recipe.qty;
                });
            });
            var panel = document.getElementById('order-consumption-panel');
            var content = document.getElementById('order-consumption-content');
            var rows = Object.values(totals);
            if (!rows.length) { panel.style.display = 'none'; return; }
            panel.style.display = '';
            content.innerHTML = '<div class="data-table-container"><table class="data-table"><thead><tr><th>Insumo</th><th>Stock actual</th><th>Consume</th><th>Queda</th></tr></thead><tbody>' + rows.map(function(item) {
                var remaining = item.stock - item.quantity;
                var state = remaining < 0 ? 'text-danger' : (remaining < item.safety ? 'text-warning' : 'text-success');
                return '<tr><td>' + item.name + ' <span class="text-muted">(' + item.unit + ')</span></td><td>' + item.stock.toLocaleString('es-CL') + '</td><td>' + item.quantity.toLocaleString('es-CL') + '</td><td class="' + state + '"><strong>' + remaining.toLocaleString('es-CL') + '</strong></td></tr>';
            }).join('') + '</tbody></table></div>';
        }

        document.addEventListener('input', function(e) {
            if (e.target.closest('#lines-table')) { updateConsumptionPanel(); updateOrderSummary(); }
            if (e.target.name?.includes('[value]')) updateOrderSummary();
        });
        document.addEventListener('change', function(e) {
            if (e.target.closest('#lines-table')) { updateConsumptionPanel(); updateOrderSummary(); }
            if (e.target.name?.includes('[kind]')) updateOrderSummary();
        });

        var selectedFiles = [];

        var customerSelect = document.querySelector('select[name="customer_id"]');
        var storeSelect = document.querySelector('select[name="store_id"]');
        var customerSearch = document.getElementById('customer-search');
        customerSearch?.addEventListener('input', function() {
            var term = this.value.toLowerCase();
            Array.from(customerSelect.options).forEach(function(option) {
                if (!option.value) return;
                option.hidden = !option.textContent.toLowerCase().includes(term);
            });
            var match = Array.from(customerSelect.options).find(function(option) { return option.value && !option.hidden; });
            if (match && term) { customerSelect.value = match.value; customerSelect.dispatchEvent(new Event('change')); }
        });
        function filterStoresByCustomer() {
            if (!customerSelect || !storeSelect) return;
            var customerId = customerSelect.value;
            Array.from(storeSelect.options).forEach(function(option) {
                if (!option.value) { option.hidden = false; return; }
                option.hidden = Boolean(customerId && option.dataset.customerId !== customerId);
            });
            if (storeSelect.selectedOptions[0]?.hidden) storeSelect.value = '';
        }
        customerSelect?.addEventListener('change', function() { filterStoresByCustomer(); fillNegotiatedPrices(); });
        filterStoresByCustomer();

        function renderPreview() {
            var preview = document.getElementById('attachments-preview');
            if (!preview) return;
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

        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('lines-table');
            const tbody = table.querySelector('tbody');
            const addBtn = document.getElementById('add-line-btn');
            let lineIndex = 1;

            addBtn.addEventListener('click', function() {
                const row = document.createElement('tr');
                row.classList.add('line-row');
                row.innerHTML = `
                    <td>
                        <select name="lines[${lineIndex}][product_id]" class="form-control">
                            <option value="">Seleccionar producto</option>
                            {!! $productOptions !!}
                        </select>
                    </td>
                    <td>
                        <input type="number" step="1" min="0" name="lines[${lineIndex}][boxes]" class="form-control" value="" placeholder="Cajas">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="lines[${lineIndex}][price_box]" class="form-control" value="" placeholder="Automático">
                    </td>
                    <td class="line-cost">$0</td><td class="line-subtotal">$0</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-line" title="Eliminar línea">&times;</button>
                    </td>
                `;
                tbody.appendChild(row);
                lineIndex++;
                updateOrderSummary();
            });

            tbody.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-remove-line')) {
                    const rows = tbody.querySelectorAll('.line-row');
                    if (rows.length > 1) {
                        e.target.closest('tr').remove();
                        updateOrderSummary();
                    }
                }
            });

            var fileInput = document.getElementById('attachments-input');

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

            var form = fileInput.closest('form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (document.querySelector('#order-consumption-content .text-danger') && !window.confirm('Este pedido dejará uno o más insumos sin stock. ¿Deseas guardarlo de todas formas?')) return;
                var formData = new FormData(form);
                formData.delete('files[]');
                selectedFiles.forEach(function(f) { formData.append('files[]', f); });
                var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch('/pedidos', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then(function(r) {
                    if (!r.ok) { return r.json().then(function(d) { throw d; }); }
                    return r.json();
                })
                .then(function() { window.location.href = '/pedidos'; })
                .catch(function(err) {
                    if (err && err.errors) {
                        var msgs = Object.values(err.errors).flat().join('\n');
                        Swal.fire({ icon: 'error', title: 'Error de validación', text: msgs });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo crear el pedido.' });
                    }
                });
            });
        });
    </script>
</x-erp-layout>
