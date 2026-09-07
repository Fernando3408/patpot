@if(!request()->ajax())
<x-erp-layout title="Editar pedido" subtitle="Puedes editar pedidos mientras no tengan despachos registrados.">
    
    <div class="form-card">
        <form method="POST" action="{{ route('pedidos.update', $order) }}">
            @csrf 
            @method('PUT')

            {{-- Datos del Encabezado del Pedido --}}
            <div class="form-grid mb-4">
                <div class="form-group">
                    <label class="form-label">Número pedido</label>
                    <input name="number" class="form-control" value="{{ old('number', $order->number) }}" required placeholder="PED-0001">
                </div>

                <div class="form-group">
                    <label class="form-label">Cliente</label>
                    <select name="customer_id" class="form-control" required>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $order->customer_id) == $customer->id)>
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
                            <option value="{{ $store->id }}" @selected(old('store_id', $order->store_id) == $store->id)>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha pedido</label>
                    <input type="date" name="ordered_on" class="form-control" value="{{ old('ordered_on', $order->ordered_on->format('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha entrega</label>
                    <input type="date" name="delivery_on" class="form-control" value="{{ old('delivery_on', $order->delivery_on?->format('Y-m-d')) }}">
                </div>
            </div>

            {{-- Observaciones --}}
            <div class="form-group mb-4">
                <label class="form-label">Observaciones</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Notas adicionales del pedido...">{{ old('notes', $order->notes) }}</textarea>
            </div>

            <div class="mb-6">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Productos del pedido</h3>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="th-cajas text-right">Cajas</th>
                                <th class="th-precio text-right">Precio/caja</th>
                                <th class="text-right">Despachado</th>
                                <th class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->lines as $index => $line)
                                @php $hasDispatch = (int) $line->dispatched_boxes > 0; @endphp
                                <tr>
                                    <td>
                                        <input type="hidden" name="lines[{{ $index }}][id]" value="{{ $line->id }}">
                                        @if($hasDispatch)
                                            <input type="hidden" name="lines[{{ $index }}][product_id]" value="{{ $line->product_id }}">
                                            <strong>{{ $line->product?->name ?? '—' }}</strong>
                                            <br><span class="text-xs text-muted">Producto bloqueado por despacho</span>
                                        @else
                                            <select name="lines[{{ $index }}][product_id]" class="form-control" required>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" @selected(old("lines.$index.product_id", $line->product_id) == $product->id)>{{ $product->name }} · base ${{ number_format($product->sale_price_box, 0, ',', '.') }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <input type="number" step="1" min="{{ $hasDispatch ? (int) $line->dispatched_boxes : 1 }}" name="lines[{{ $index }}][boxes]" class="form-control form-control-sm text-right input-sm-narrow" value="{{ old("lines.$index.boxes", $line->boxes) }}" required>
                                    </td>
                                    <td class="text-right">
                                        <input type="number" step="0.01" min="0" name="lines[{{ $index }}][price_box]" class="form-control form-control-sm text-right input-sm-narrow" value="{{ old("lines.$index.price_box", $line->price_box) }}" @readonly($hasDispatch)>
                                    </td>
                                    <td class="text-right font-bold">{{ number_format($line->dispatched_boxes, 0, ',', '.') }}</td>
                                    <td class="text-right">
                                        @if(! $hasDispatch)
                                            <label class="text-xs text-muted"><input type="checkbox" name="lines[{{ $index }}][remove]" value="1"> Quitar</label>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @foreach(range($order->lines->count(), $order->lines->count() + 2) as $index)
                                <tr>
                                    <td>
                                        <select name="lines[{{ $index }}][product_id]" class="form-control">
                                            <option value="">Sin línea</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" @selected(old("lines.$index.product_id") == $product->id)>{{ $product->name }} · base ${{ number_format($product->sale_price_box, 0, ',', '.') }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-right">
                                        <input type="number" step="1" min="1" name="lines[{{ $index }}][boxes]" class="form-control form-control-sm text-right input-sm-narrow" value="{{ old("lines.$index.boxes") }}">
                                    </td>
                                    <td class="text-right">
                                        <input type="number" step="0.01" min="0" name="lines[{{ $index }}][price_box]" class="form-control form-control-sm text-right input-sm-narrow" value="{{ old("lines.$index.price_box") }}" placeholder="Auto">
                                    </td>
                                    <td class="text-right text-muted">—</td>
                                    <td></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-muted mt-2">Los precios se pactan por cliente y producto. Este pedido deja de ser editable apenas registra un despacho.</p>
            </div>

            {{-- Botones de Acción --}}
            <div class="form-actions">
                <a href="/pedidos" class="btn btn-outline-warning">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>

</x-erp-layout>
@endif
