<x-erp-layout title="Recetas / BOM" subtitle="Define los ingredientes y cantidades necesarias para cada producto.">
    <div class="page-header">
        <div class="page-header-actions">
            <a href="/recetas/create" class="btn btn-outline-primary btn-sm">+ Agregar insumo a receta</a>
        </div>
    </div>

    @if($recipes->count() > 0)
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th>Código</th>
                        <th class="text-right">Cantidad/caja</th>
                        <th>Unidad</th>
                        <th class="text-right">Costo/caja</th>
                        <th>Capacidad</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recipes as $recipe)
                        <tr>
                            <td class="font-bold">{{ $recipe->product->name }}</td>
                            <td class="text-xs">{{ $recipe->product->sku }}</td>
                            <td class="text-xs">{{ $recipe->input->code }}</td>
                            <td class="text-right font-bold">{{ $recipe->qty_per_box == floor($recipe->qty_per_box) ? number_format($recipe->qty_per_box, 0, ',', '.') : number_format($recipe->qty_per_box, 2, ',', '.') }}</td>
                            <td>{{ $recipe->input->unit }}</td>
                            <td class="text-right font-bold">${{ number_format($recipe->qty_per_box * (float) $recipe->input->unit_cost, 0, ',', '.') }}</td>
                            <td class="text-xs">{{ $recipe->product->production_capacity ? number_format($recipe->product->production_capacity, 0, ',', '.') . ' cajas' : '—' }}</td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <button type="button" class="btn btn-outline-info btn-sm btn-detail-modal" data-url="{{ route('recipes.show', $recipe->product) }}" data-title="Receta: {{ $recipe->product->name }}">Ver detalle</button>
                                    <a href="/recetas/{{ $recipe->product->id }}/edit" class="btn btn-outline-success btn-sm btn-edit-modal" data-url="/recetas/{{ $recipe->product->id }}/edit" data-title="Editar receta">Editar</a>
                                    @if(auth()->user()->canManage())
                                        <form method="POST" action="/recetas/{{ $recipe->id }}" style="display: inline;">
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
                <p>No hay ingredientes registrados en ninguna receta.</p>
            </div>
        </div>
    @endif
</x-erp-layout>