@php
    $isPartial = request()->ajax() || request()->has('_partial');
@endphp
@if(!$isPartial)
<x-erp-layout title="Editar compra" subtitle="Puedes editar líneas sin recepción. Las líneas ya recibidas quedan bloqueadas para conservar la historia.">
@endif
    <div class="form-card">
        <form method="POST" action="{{ route('compras.update', $purchase) }}">
            @csrf
            @method('PUT')

            <div class="form-grid mb-4">
                <div class="form-group">
                    <label class="form-label">Numero OC</label>
                    <input name="number" class="form-control" value="{{ old('number', $purchase->number) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Proveedor</label>
                    <select name="supplier_id" class="form-control" required>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchase->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha emision</label>
                    <input type="date" name="ordered_on" class="form-control" value="{{ old('ordered_on', $purchase->ordered_on->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Entrega estimada</label>
                    <input type="date" name="expected_on" class="form-control" value="{{ old('expected_on', $purchase->expected_on?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Observaciones</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $purchase->notes) }}</textarea>
            </div>

            @if($purchase->lines->isNotEmpty())
                <div class="mb-4">
                    <h3 class="section-subtitle">Items de compra</h3>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th class="th-cantidad text-right">Cantidad</th>
                                    <th class="text-right">Costo unitario</th>
                                    <th class="text-right">Recibido</th>
                                    <th class="text-right"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->lines as $index => $line)
                                    @php $isReceived = (float) $line->received_quantity > 0; @endphp
                                    <tr>
                                        <td>
                                            <input type="hidden" name="lines[{{ $index }}][id]" value="{{ $line->id }}">
                                            @if($isReceived)
                                                <input type="hidden" name="lines[{{ $index }}][input_id]" value="{{ $line->input_id }}">
                                                <strong>{{ $line->input->name ?? '—' }}</strong>
                                                <br><span class="text-xs text-muted">Bloqueada por recepción</span>
                                            @else
                                                <select name="lines[{{ $index }}][input_id]" class="form-control" required>
                                                    @foreach($inputs as $input)
                                                        <option value="{{ $input->id }}" @selected(old("lines.$index.input_id", $line->input_id) == $input->id)>{{ $input->name }} · {{ $input->unit }}</option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <input type="number" step="1" min="1" name="lines[{{ $index }}][ordered_quantity]" class="form-control form-control-sm text-right input-sm-narrow" value="{{ old("lines.$index.ordered_quantity", (int) $line->ordered_quantity) }}" @readonly($isReceived) required>
                                        </td>
                                        <td class="text-right">
                                            <input type="number" step="1" min="0" name="lines[{{ $index }}][unit_cost]" class="form-control form-control-sm text-right input-sm-narrow" value="{{ old("lines.$index.unit_cost", (int) $line->unit_cost) }}" @readonly($isReceived) required>
                                        </td>
                                        <td class="text-right font-bold">{{ $line->input?->formattedQuantity($line->received_quantity) ?? number_format($line->received_quantity, 0, ',', '.') }}</td>
                                        <td class="text-right">
                                            @if(! $isReceived)
                                                <label class="text-xs text-muted"><input type="checkbox" name="lines[{{ $index }}][remove]" value="1"> Quitar</label>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                @foreach(range($purchase->lines->count(), $purchase->lines->count() + 2) as $index)
                                    <tr>
                                        <td>
                                            <select name="lines[{{ $index }}][input_id]" class="form-control">
                                                <option value="">Sin línea</option>
                                                @foreach($inputs as $input)
                                                    <option value="{{ $input->id }}" @selected(old("lines.$index.input_id") == $input->id)>{{ $input->name }} · {{ $input->unit }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-right">
                                            <input type="number" step="1" min="1" name="lines[{{ $index }}][ordered_quantity]" class="form-control form-control-sm text-right input-sm-narrow" value="{{ old("lines.$index.ordered_quantity") }}">
                                        </td>
                                        <td class="text-right">
                                            <input type="number" step="1" min="0" name="lines[{{ $index }}][unit_cost]" class="form-control form-control-sm text-right input-sm-narrow" value="{{ old("lines.$index.unit_cost") }}">
                                        </td>
                                        <td class="text-right text-muted">—</td>
                                        <td></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-muted mt-2">Las líneas con recepción registrada no se pueden modificar ni eliminar.</p>
                </div>
            @endif

            <div class="form-actions">
                <a href="/compras" class="btn btn-outline-warning">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>

@if(!$isPartial)
</x-erp-layout>
@endif
