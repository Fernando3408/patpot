<x-erp-layout title="Productos" subtitle="Gestiona el catálogo de productos con sus especificaciones y costos.">
    <div class="page-header">
        <div class="page-header-actions">
            <a href="{{ route('products.create') }}" class="btn btn-outline-primary btn-sm">+ Nuevo producto</a>
        </div>
    </div>

    @if($products->count() > 0)
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>SKU</th>
                        <th class="text-right">Stock</th>
                        <th class="text-right">Precio</th>
                        <th class="text-right">Costo</th>
                        <th class="text-right">Margen</th>
                        <th class="text-right">Capacidad</th>
                        <th>Estado</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr data-update-url="{{ route('products.update', $product) }}">
                            <td data-field="name" class="font-bold">{{ $product->name }}</td>
                            <td data-field="sku" class="text-xs">{{ $product->sku }}</td>
                            <td data-field="stock_boxes" data-cleanup="int" class="text-right font-bold">{{ number_format($product->stock_boxes, 0, ',', '.') }} cajas</td>
                            <td data-field="sale_price_box" class="text-right font-bold">${{ number_format($product->sale_price_box, 0, ',', '.') }}</td>
                            <td data-field="cost_per_box" data-readonly="true" class="text-right font-bold">${{ number_format($product->cost_per_box, 0, ',', '.') }}</td>
                            @php
                                $margin = $product->sale_price_box - $product->cost_per_box;
                                $marginPct = $product->sale_price_box > 0 ? round($margin / $product->sale_price_box * 100, 1) : 0;
                            @endphp
                            <td class="text-right">
                                <span class="{{ $margin >= 0 ? 'text-positive' : 'text-negative' }} fw-600">${{ number_format($margin, 0, ',', '.') }}</span>
                                <span class="text-xs text-muted">{{ $marginPct }}%</span>
                            </td>
                            <td class="text-right">
                                @php $cap = $product->production_capacity; @endphp
                                @if($cap !== null)
                                    <strong>{{ number_format($cap, 0, ',', '.') }}</strong> <span class="text-xs text-muted">cajas</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td data-field="status" data-type="select" data-options='[{"value":"active","label":"Activo"},{"value":"inactive","label":"Inactivo"}]'>
                                <span class="badge @if($product->status === 'active') badge-success @else badge-danger @endif">
                                    {{ $product->status === 'active' ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <a href="/recetas/{{ $product->id }}/edit" class="btn btn-outline-success btn-sm">Receta</a>
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-inline" onclick="enableInlineEdit(this.closest('tr'))">Editar</button>
                                    <button type="button" class="btn btn-outline-info btn-sm btn-detail-modal" data-url="{{ route('products.show', $product) }}" data-title="Detalle: {{ $product->name }}">Ver detalle</button>
                                    @if(auth()->user()->canManage())
                                        <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline-form" style="display:inline;">
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
                <p>No hay productos registrados.</p>
            </div>
        </div>
    @endif
</x-erp-layout>
