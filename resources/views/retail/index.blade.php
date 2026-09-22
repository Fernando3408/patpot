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
                        <tr>
                            <td class="font-bold">{{ $r->store?->code }} — {{ $r->store?->customer?->trade_name ?? $r->store?->customer?->business_name }}</td>
                            <td>{{ $r->product?->name }} <span class="text-xs text-muted">({{ $r->product?->sku }})</span></td>
                            <td class="text-right font-bold">{{ number_format($r->stock_units, 0, ',', '.') }}</td>
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
                            <td class="text-right">{{ number_format($r->suggested_replenishment_boxes, 0, ',', '.') }} cajas</td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <button type="button" class="btn btn-outline-info btn-sm btn-detail-modal" data-url="{{ route('retail.show', $r) }}" data-title="Detalle: Retail {{ $r->store?->code }}">Ver detalle</button>
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-modal" data-url="{{ route('retail.edit', $r) }}" data-title="Editar: Retail">Editar</button>
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
