<x-erp-layout title="Movimientos de inventario" subtitle="Historial de todos los ajustes, recepciones, consumos y despachos.">

    <div class="page-header">
        <form method="GET" class="search-form">
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            <select name="kind" class="form-control">
                <option value="">Todos los tipos</option>
                @foreach(['Recepción de compra', 'Consumo de producción', 'Ingreso producto terminado', 'Despacho de pedido', 'Ajuste'] as $k)
                    <option value="{{ $k }}" {{ request('kind') === $k ? 'selected' : '' }}>{{ $k }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline-success btn-sm">Filtrar</button>
            @if(request()->hasAny(['from', 'to', 'kind']))
                <a href="{{ route('movements.index') }}" class="btn btn-outline-warning btn-sm">Limpiar</a>
            @endif
        </form>
    </div>

    @if($movements->count() > 0)
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Insumo / Producto</th>
                        <th class="text-right">Cantidad</th>
                        <th>Referencia</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $m)
                        <tr>
                            <td class="text-xs">{{ $m->created_at?->format('d-m-Y H:i') ?? '—' }}</td>
                            <td><span class="badge badge-info">{{ $m->kind }}</span></td>
                            <td>
                                @if($m->input)
                                    <strong>{{ $m->input->name }}</strong>
                                    <span class="text-xs text-muted"> · {{ $m->input->code }}</span>
                                @elseif($m->product)
                                    <strong>{{ $m->product->name }}</strong>
                                    <span class="text-xs text-muted"> · {{ $m->product->sku }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-right font-bold" style="color: {{ $m->quantity >= 0 ? '#166534' : '#991b1b' }}">
                                {{ $m->quantity >= 0 ? '+' : '' }}{{ number_format($m->quantity, 2, ',', '.') }}
                            </td>
                            <td class="text-xs">{{ $m->reference ?? '—' }}</td>
                            <td class="text-xs">{{ $m->user?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1rem;">
            {{ $movements->links() }}
        </div>
    @else
        <div class="table-container">
            <div class="data-table-empty">
                <p>No hay movimientos de inventario.</p>
            </div>
        </div>
    @endif
</x-erp-layout>
