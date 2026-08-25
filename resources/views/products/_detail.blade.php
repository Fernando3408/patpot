<div class="card">
    <div class="card__header">
        <h2 class="card__title">Producto</h2>
    </div>
    <div class="card__body">
        <div class="form-grid">
            <div><strong>Nombre:</strong> {{ $product->name }}</div>
            <div><strong>SKU:</strong> {{ $product->sku }}</div>
            <div><strong>Gramos:</strong> {{ number_format($product->grams, 0, ',', '.') }} g</div>
            <div><strong>Unidades por caja:</strong> {{ number_format($product->units_per_box, 0, ',', '.') }}</div>
            <div><strong>Stock cajas:</strong> {{ number_format($product->stock_boxes, 0, ',', '.') }}</div>
            <div><strong>Stock mínimo:</strong> {{ number_format($product->min_stock_boxes, 0, ',', '.') }}</div>
            <div><strong>Precio venta/caja:</strong> ${{ number_format($product->sale_price_box, 0, ',', '.') }}</div>
            <div><strong>Costo/caja:</strong> ${{ number_format($product->cost_per_box, 0, ',', '.') }}</div>
            <div><strong>Capacidad producción:</strong> {{ $product->production_capacity ? number_format($product->production_capacity, 0, ',', '.') . ' cajas' : '—' }}</div>
            <div>
                <strong>Estado:</strong>
                <span class="badge {{ $product->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                    {{ $product->status === 'active' ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
    </div>
</div>

@if($product->recipes->count())
<div class="card mt-4">
    <div class="card__header">
        <h2 class="card__title">Receta ({{ $product->recipes->count() }} insumos)</h2>
    </div>
    <div class="card__body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Insumo</th>
                    <th class="text-right">Cant./caja</th>
                    <th class="text-right">Costo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($product->recipes as $recipe)
                    <tr>
                        <td>{{ $recipe->input?->name ?? '—' }}</td>
                        <td class="text-right">{{ $recipe->qty_per_box == floor($recipe->qty_per_box) ? number_format($recipe->qty_per_box, 0, ',', '.') : number_format($recipe->qty_per_box, 2, ',', '.') }}</td>
                        <td class="text-right">${{ number_format($recipe->qty_per_box * (float) ($recipe->input?->unit_cost ?? 0), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
