<x-erp-layout title="Retail y Quiebres" subtitle="Monitorea el stock en punto de venta y detecta quiebres de inventario.">
    <div class="page-header">
        <div class="page-header-actions">
            <a href="{{ route('retail.create') }}" class="btn btn-outline-primary btn-sm">+ Nuevo registro retail</a>
        </div>
    </div>

    @if($records->count() > 0)
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sala</th>
                        <th>Producto</th>
                        <th class="text-right">Stock</th>
                        <th>Quiebre</th>
                        <th class="text-right">Reposición</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $r)
                        <tr data-update-url="{{ route('retail.update', $r) }}">
                            <td class="font-bold">{{ $r->store?->code }} — {{ $r->store?->customer?->trade_name ?? $r->store?->customer?->business_name }}</td>
                            <td>{{ $r->product?->name }} <span class="text-xs text-muted">({{ $r->product?->sku }})</span></td>
                            <td data-field="stock_units" class="text-right font-bold">{{ number_format($r->stock_units, 0, ',', '.') }}</td>
                            <td>
                                @if($r->is_break)
                                    <span class="badge alert-danger">QUIEBRE</span>
                                @elseif($r->isInTransit)
                                    <span class="badge badge-warning">EN TRÁNSITO</span>
                                @elseif($r->isWarning)
                                    <span class="badge badge-warning">ATENCIÓN</span>
                                @else
                                    <span class="badge badge-success">OK</span>
                                @endif
                            </td>
                            <td data-field="suggested_replenishment_boxes" class="text-right">{{ number_format($r->suggested_replenishment_boxes, 0, ',', '.') }} cajas</td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="showInlineDetail(this)" data-title="Detalle: Retail {{ $r->store?->code }}">Ver detalle</button>
                                    <template>
                                        <div class="card">
                                            <div class="card__header"><h2 class="card__title">Retail</h2></div>
                                            <div class="card__body">
                                                <div class="form-grid">
                                                    <div><strong>Sala:</strong> {{ $r->store?->code }} — {{ $r->store?->customer?->trade_name ?? $r->store?->customer?->business_name ?? '—' }}</div>
                                                    <div><strong>Producto:</strong> {{ $r->product?->name }} ({{ $r->product?->sku ?? '—' }})</div>
                                                    <div><strong>Catalogado:</strong> {{ $r->cataloged ? 'Sí' : 'No' }}</div>
                                                    <div><strong>Stock unidades:</strong> {{ number_format($r->stock_units, 0, ',', '.') }}</div>
                                                    <div><strong>Tránsito unidades:</strong> {{ number_format($r->transit_units, 0, ',', '.') }}</div>
                                                    <div><strong>Venta semanal:</strong> {{ number_format($r->weekly_sales, 0, ',', '.') }}</div>
                                                    <div><strong>Cobertura:</strong> {{ $r->coverage_weeks !== null ? number_format($r->coverage_weeks, 1, ',', '.') . ' semanas' : '—' }}</div>
                                                    <div><strong>Quiebre:</strong>
                                                        @if($r->is_break)
                                                            <span class="badge alert-danger">QUIEBRE</span>
                                                        @else
                                                            <span class="badge badge-success">No</span>
                                                        @endif
                                                    </div>
                                                    <div><strong>Reposición sugerida:</strong> {{ number_format($r->suggested_replenishment_boxes, 0, ',', '.') }} cajas</div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-inline" onclick="enableInlineEdit(this.closest('tr'))">Editar</button>
                                    @if(auth()->user()->canManage())
                                        <form method="POST" action="{{ route('retail.destroy', $r) }}" class="inline-form" style="display:inline;">
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
                <p>No hay registros retail.</p>
            </div>
        </div>
    @endif
</x-erp-layout>
