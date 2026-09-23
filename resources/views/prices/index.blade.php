<x-erp-layout title="Precios por Cliente" subtitle="Gestiona precios especiales por cliente y producto con ofertas vigentes.">
    <div class="page-header">
        <div class="page-header-filters">
            <form method="GET" action="{{ route('precios.index') }}" class="search-form">
                <select name="customer_id" class="form-control">
                    <option value="">Todos los clientes</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->business_name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline-success btn-sm">Filtrar</button>
                @if(request('customer_id'))
                    <a href="{{ route('precios.index') }}" class="btn btn-outline-warning btn-sm">Limpiar</a>
                @endif
            </form>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('precios.create') }}" class="btn btn-outline-primary btn-sm">+ Nuevo precio</a>
        </div>
    </div>

    @if ($prices->isNotEmpty())
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Producto</th>
                        <th class="text-right">Precio</th>
                        <th class="text-right">Oferta</th>
                        <th>Vigente hasta</th>
                        <th>Estado</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($prices as $price)
                        <tr>
                            <td>
                                <strong>{{ $price->customer?->trade_name ?? $price->customer?->business_name ?? '—' }}</strong>
                                @if ($price->customer?->code)
                                    <br><span class="text-xs text-muted">{{ $price->customer->code }}</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $price->product?->name ?? '—' }}</strong>
                                @if ($price->product?->sku)
                                    <br><span class="text-xs text-muted">{{ $price->product->sku }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <strong>${{ number_format($price->price_box, 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-right">
                                @if ($price->offer_price)
                                    <strong class="text-price">${{ number_format($price->offer_price, 0, ',', '.') }}</strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($price->offer_until)
                                    {{ $price->offer_until->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($price->offer_until)
                                    @if ($price->offer_until < now()->toDateString())
                                        <span class="badge badge-warning">Vencida</span>
                                    @else
                                        <span class="badge badge-success">Vigente</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <button type="button" class="btn btn-outline-info btn-sm btn-detail-modal" data-url="{{ route('precios.show', $price) }}" data-title="Detalle: Precio">Ver detalle</button>
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-modal" data-url="{{ route('precios.edit', $price) }}" data-title="Editar: Precio">Editar</button>
                                    @if(auth()->user()->canManage())
                                        <form method="POST" action="{{ route('precios.destroy', $price) }}" class="inline-form" style="display:inline;">
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
                <p>No hay precios registrados.<br><span class="text-xs">Los pedidos usarán el precio base del producto.</span></p>
            </div>
        </div>
    @endif
</x-erp-layout>
