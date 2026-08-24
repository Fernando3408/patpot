<x-erp-layout title="Precios por Cliente" subtitle="Gestiona precios especiales por cliente y producto con ofertas vigentes.">
    <div class="page-header">
        <form method="GET" action="{{ route('precios.index') }}" class="search-form">
            <select name="customer_id" class="form-control">
                <option value="">Todos los clientes</option>
                @foreach(\App\Models\Customer::where('status', true)->orderBy('business_name')->get() as $c)
                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->business_name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline-success btn-sm">Filtrar</button>
            @if(request('customer_id'))
                <a href="{{ route('precios.index') }}" class="btn btn-outline-warning btn-sm">Limpiar</a>
            @endif
        </form>
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
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($prices as $price)
                        <tr data-update-url="{{ route('precios.update', $price) }}">
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
                            <td class="text-right" data-field="price_box">
                                <strong>${{ number_format($price->price_box, 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-right" data-field="offer_price">
                                @if ($price->offer_price)
                                    <strong style="color: #6a9c3b;">${{ number_format($price->offer_price, 0, ',', '.') }}</strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td data-field="offer_until" data-type="date">
                                @if ($price->offer_until)
                                    {{ $price->offer_until->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td data-readonly="true">
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
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="showInlineDetail(this)" data-title="Detalle: Precio">Ver detalle</button>
                                    <template>
                                        <div class="card">
                                            <div class="card__header"><h2 class="card__title">Precio por Cliente</h2></div>
                                            <div class="card__body">
                                                <div class="form-grid">
                                                    <div><strong>Cliente:</strong> {{ $price->customer?->business_name ?? '—' }}</div>
                                                    <div><strong>Producto:</strong> {{ $price->product?->name ?? '—' }}</div>
                                                    <div><strong>Precio base:</strong> ${{ number_format($price->price_box, 0, ',', '.') }}</div>
                                                    <div><strong>Precio oferta:</strong> {{ $price->offer_price ? '$' . number_format($price->offer_price, 0, ',', '.') : '—' }}</div>
                                                    <div><strong>Vigencia hasta:</strong> {{ $price->offer_until?->format('d/m/Y') ?? '—' }}</div>
                                                    <div><strong>Precio efectivo:</strong> <strong>${{ number_format($price->effective_price, 0, ',', '.') }}</strong></div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-inline" onclick="enableInlineEdit(this.closest('tr'))">Editar</button>
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
