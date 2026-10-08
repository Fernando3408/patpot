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
            <div><strong>Costo piso/caja:</strong> {{ $product->production_cost !== null ? '$' . number_format($product->production_cost, 0, ',', '.') : 'Calculado por receta' }}</div>
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
                        <td class="text-right">{{ rtrim(rtrim(rtrim(number_format($recipe->qty_per_box, 3, ',', '.'), '0'), '.'), ',') }}</td>
                        <td class="text-right">${{ number_format($recipe->qty_per_box * (float) ($recipe->input?->unit_cost ?? 0), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="card mt-4">
    <div class="card__body">
        <form method="POST" action="{{ route('products.recipes.copy', $product) }}" class="form-grid">
            @csrf
            <select name="source_product_id" class="form-control" required>
                <option value="">Copiar receta desde otro producto</option>
                @foreach($recipeSources as $source)
                    <option value="{{ $source->id }}">{{ $source->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-primary">Copiar receta</button>
        </form>
    </div>
</div>
@else
<div class="card mt-4">
    <div class="card__body">
        <span class="badge badge-warning">Receta incompleta</span>
        <p class="text-muted mt-2 mb-0">Este producto no puede utilizarse en nuevos pedidos hasta configurar sus insumos y cantidades por caja.</p>
    </div>
</div>
@endif
