<div class="card">
    <div class="card__header">
        <h2 class="card__title">Insumo</h2>
    </div>
    <div class="card__body">
        <div class="form-grid">
            <div><strong>Código:</strong> {{ $input->code }}</div>
            <div><strong>Nombre:</strong> {{ $input->name }}</div>
            <div><strong>Categoría:</strong> {{ $input->category ?? '—' }}</div>
            <div><strong>Unidad:</strong> {{ $input->unit }}</div>
            <div><strong>Stock:</strong> {{ number_format($input->stock, 0, ',', '.') }}</div>
            <div><strong>Stock seguridad:</strong> {{ number_format($input->safety_stock, 0, ',', '.') }}</div>
            <div><strong>Tránsito:</strong> {{ number_format($input->transit ?? 0, 0, ',', '.') }}</div>
            <div><strong>Costo unitario:</strong> ${{ number_format($input->unit_cost, 0, ',', '.') }}</div>
            <div><strong>Consumo semanal:</strong> {{ number_format($input->weekly_consumption, 0, ',', '.') }}</div>
            <div><strong>Lead time:</strong> {{ $input->lead_time_days }} días</div>
            <div><strong>Punto reposición:</strong> {{ number_format($input->reorder_point, 0, ',', '.') }}</div>
            <div><strong>Semanas objetivo:</strong> {{ $input->target_weeks ?? '—' }}</div>
            <div><strong>Compra mínima:</strong> {{ number_format($input->min_purchase ?? 0, 0, ',', '.') }}</div>
            <div><strong>Múltiplo compra:</strong> {{ number_format($input->purchase_multiple ?? 0, 0, ',', '.') }}</div>
            <div>
                <strong>Estado:</strong>
                <span class="badge {{ $input->status ? 'badge-success' : 'badge-danger' }}">
                    {{ $input->status ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
    </div>
</div>

@if($input->supplier)
<div class="card mt-4">
    <div class="card__header">
        <h2 class="card__title">Proveedor</h2>
    </div>
    <div class="card__body">
        <div class="form-grid">
            <div><strong>Nombre:</strong> {{ $input->supplier->name }}</div>
            <div><strong>Contacto:</strong> {{ $input->supplier->contact_name ?? '—' }}</div>
            <div><strong>Email:</strong> {{ $input->supplier->email ?? '—' }}</div>
        </div>
    </div>
</div>
@endif

@if($input->recipes->count())
<div class="card mt-4">
    <div class="card__header">
        <h2 class="card__title">Recetas ({{ $input->recipes->count() }} productos)</h2>
    </div>
    <div class="card__body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-right">Cant./caja</th>
                </tr>
            </thead>
            <tbody>
                @foreach($input->recipes as $recipe)
                    <tr>
                        <td>{{ $recipe->product?->name ?? '—' }}</td>
                        <td class="text-right">{{ number_format($recipe->qty_per_box, 4, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
