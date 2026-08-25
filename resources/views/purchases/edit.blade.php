@if(!request()->ajax())
<x-erp-layout title="Editar compra" subtitle="{{ $canEditLines ? 'Modifica las cantidades de los items que aun no se han recepcionado.' : 'Las recepciones ya iniciaron, no puedes editar las cantidades.' }}">
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
                    <h3 class="section-subtitle">Items — cantidad pedida</h3>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th class="th-cantidad text-right">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->lines as $line)
                                    <tr>
                                        <td class="font-bold">{{ $line->input->name ?? '—' }} <span class="text-xs text-muted">x{{ number_format($line->unit_cost, 0, ',', '.') }}</span></td>
                                        <td class="text-right">
                                            @if($canEditLines)
                                                <input type="number" name="lines[{{ $line->id }}][ordered_quantity]" class="form-control form-control-sm text-right input-sm-narrow" value="{{ $line->ordered_quantity }}" min="1" required>
                                            @else
                                                <span class="font-bold">{{ $line->ordered_quantity }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(!$canEditLines)
                        <p class="text-xs text-muted mt-2">No puedes editar cantidades porque ya se recepcionaron items.</p>
                    @endif
                </div>
            @endif

            <div class="form-actions">
                <a href="/compras" class="btn btn-outline-warning">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>

</x-erp-layout>
@endif
