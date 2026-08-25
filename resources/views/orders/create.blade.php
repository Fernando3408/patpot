<x-erp-layout title="Nuevo pedido" subtitle="Puedes cargar tantos productos como necesites. Si dejas el precio vacío, se aplica el precio vigente del cliente o el precio base.">
    
    <div class="form-card">
        <form method="POST" action="/pedidos">
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
        });
    </script>
</x-erp-layout>