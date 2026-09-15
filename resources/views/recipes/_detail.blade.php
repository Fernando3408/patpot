@if(!request()->ajax())
    <x-erp-layout title="Detalle receta: {{ $product->name }}" subtitle="Ingredientes y cantidades de la receta.">
        <div class="card">
            <div class="card__header">
                <h2 class="card__title">Producto</h2>
            </div>
            <div class="card__body">
                <div class="form-grid">
                    <div><strong>Nombre:</strong> {{ $product->name }}</div>
                    <div><strong>SKU:</strong> {{ $product->sku }}</div>
                    <div><strong>Formato:</strong> {{ number_format($product->units_per_box, 0, ',', '.') }} un/caja</div>
                    <div><strong>Capacidad:</strong> {{ $product->production_capacity ? number_format($product->production_capacity, 0, ',', '.') . ' cajas' : '—' }}</div>
                </div>
            </div>
        </div>

        @if($product->recipes->isNotEmpty())
            @php
                $totalCost = 0;
            @endphp
            <div class="card mt-4">
                <div class="card__header">
                    <h2 class="card__title">Receta ({{ $product->recipes->count() }} insumos)</h2>
                </div>
                <div class="card__body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Insumo</th>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th class="text-center">Unidad</th>
                                <th class="text-right">Costo unitario</th>
                                <th class="text-right">Cantidad/caja</th>
                                <th class="text-right">Costo/caja</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->recipes as $recipe)
                                @php
                                    $lineCost = $recipe->qty_per_box * (float) $recipe->input->unit_cost;
                                    $totalCost += $lineCost;
                                @endphp
                                <tr>
                                    <td class="font-bold">{{ $recipe->input->name }}</td>
                                    <td class="text-xs">{{ $recipe->input->code }}</td>
                                    <td class="text-xs">{{ $recipe->input->type === 'service' ? 'Servicio' : 'Materia prima' }}</td>
                                    <td class="text-center text-xs">{{ $recipe->input->unit }}</td>
                                    <td class="text-right">${{ number_format((float) $recipe->input->unit_cost, 0, ',', '.') }}</td>
                                    <td class="text-right font-bold">{{ rtrim(rtrim(rtrim(number_format($recipe->qty_per_box, 3, ',', '.'), '0'), '.'), ',') }}</td>
                                    <td class="text-right font-bold">${{ number_format($lineCost, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="row-alt-bg">
                                <th colspan="6" class="text-right font-bold">Costo total por caja:</th>
                                <th class="text-right font-bold text-lg text-primary-brand">
                                    ${{ number_format($totalCost, 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @else
            <div class="card mt-4">
                <div class="card__body">
                    <div class="data-table-empty">
                        <p>Este producto no tiene ingredientes registrados.</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="form-actions">
            <a href="{{ route('recipes.index') }}" class="btn btn-outline-warning">Volver</a>
            <a href="{{ route('recipes.edit', $product) }}" class="btn btn-primary">Editar receta</a>
        </div>
    </x-erp-layout>
@else
    <div class="card">
        <div class="card__header">
            <h2 class="card__title">{{ $product->name }}</h2>
        </div>
        <div class="card__body">
            <div class="form-grid">
                <div><strong>SKU:</strong> {{ $product->sku }}</div>
                <div><strong>Formato:</strong> {{ number_format($product->units_per_box, 0, ',', '.') }} un/caja</div>
                <div><strong>Capacidad:</strong> {{ $product->production_capacity ? number_format($product->production_capacity, 0, ',', '.') . ' cajas' : '—' }}</div>
            </div>
        </div>
    </div>

    @if($product->recipes->isNotEmpty())
        @php
            $totalCost = 0;
        @endphp
        <div class="card mt-4">
            <div class="card__header">
                <h2 class="card__title">Receta ({{ $product->recipes->count() }} insumos)</h2>
            </div>
            <div class="card__body" style="overflow-x:auto;">
                <table class="data-table mb-0">
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Tipo</th>
                            <th class="text-center">Unidad</th>
                            <th class="text-right">Cant./caja</th>
                            <th class="text-right">Costo/caja</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product->recipes as $recipe)
                            @php
                                $lineCost = $recipe->qty_per_box * (float) $recipe->input->unit_cost;
                                $totalCost += $lineCost;
                            @endphp
                            <tr>
                                <td class="font-bold">{{ $recipe->input->name }}</td>
                                <td class="text-xs">{{ $recipe->input->type === 'service' ? 'Servicio' : 'Materia prima' }}</td>
                                <td class="text-center text-xs">{{ $recipe->input->unit }}</td>
                                <td class="text-right font-bold">{{ rtrim(rtrim(rtrim(number_format($recipe->qty_per_box, 3, ',', '.'), '0'), '.'), ',') }}</td>
                                <td class="text-right font-bold">${{ number_format($lineCost, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="row-alt-bg">
                            <th colspan="4" class="text-right font-bold">Costo total/caja:</th>
                            <th class="text-right font-bold text-primary-brand">
                                ${{ number_format($totalCost, 0, ',', '.') }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @else
        <div class="card mt-4">
            <div class="card__body">
                <div class="data-table-empty">
                    <p>Este producto no tiene ingredientes registrados.</p>
                </div>
            </div>
        </div>
    @endif
@endif
