<x-erp-layout title="Productos" subtitle="Gestiona el catálogo de productos con sus especificaciones y costos.">
    <div class="page-header">
        <form method="GET" action="{{ route('products.index') }}" class="search-form">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, SKU..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-success btn-sm">Buscar</button>
            @if(request('search'))
                <a href="{{ route('products.index') }}" class="btn btn-outline-warning btn-sm">Limpiar</a>
            @endif
        </form>
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
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
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
