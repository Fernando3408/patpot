@if(!request()->ajax())
    <x-erp-layout title="Detalle receta: {{ $product->name }}" subtitle="Ingredientes y cantidades de la receta.">
        <div class="form-card">
            <div class="mb-4 p-4 section-alt-bg">
                <div class="form-grid">
                    <div>
                        <span class="text-xs text-muted">Producto:</span>
                        <p class="font-bold text-lg mb-0">{{ $product->name }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-muted">SKU:</span>
                        <p class="font-bold mb-0">{{ $product->sku }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-muted">Formato:</span>
                        <p class="font-bold mb-0">{{ number_format($product->units_per_box, 0, ',', '.') }} un/caja</p>
                    </div>
                    <div>
                        <span class="text-xs text-muted">Capacidad:</span>
                        <p class="font-bold mb-0">{{ $product->production_capacity ? number_format($product->production_capacity, 0, ',', '.') . ' cajas' : '—' }}</p>
                    </div>
                </div>
            </div>

            @if($product->recipes->isNotEmpty())
                @php
                    $totalCost = 0;
                @endphp
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Insumo</th>
                                <th>Codigo</th>
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
                                    <td>{{ $recipe->input->category ?? '—' }}</td>
                                    <td class="text-center text-xs">{{ $recipe->input->unit }}</td>
                                    <td class="text-right">${{ number_format((float) $recipe->input->unit_cost, 0, ',', '.') }}</td>
                                    <td class="text-right font-bold">{{ $recipe->qty_per_box == floor($recipe->qty_per_box) ? number_format($recipe->qty_per_box, 0, ',', '.') : number_format($recipe->qty_per_box, 2, ',', '.') }}</td>
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
            @else
                <div class="data-table-empty">
                    <p>Este producto no tiene ingredientes registrados.</p>
                </div>
            @endif

            <div class="form-actions">
                <a href="{{ route('recipes.index') }}" class="btn btn-outline-warning">Volver</a>
                <a href="{{ route('recipes.edit', $product) }}" class="btn btn-primary">Editar receta</a>
            </div>
        </div>
    </x-erp-layout>
@else
    <div class="mb-4 p-4 section-alt-bg">
        <div class="form-grid">
            <div>
                <span class="text-xs text-muted">Producto:</span>
                <p class="font-bold text-lg mb-0">{{ $product->name }}</p>
            </div>
            <div>
                <span class="text-xs text-muted">SKU:</span>
                <p class="font-bold mb-0">{{ $product->sku }}</p>
            </div>
        </div>
    </div>

    @if($product->recipes->isNotEmpty())
        @php
            $totalCost = 0;
        @endphp
        <div class="table-container">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Insumo</th>
                        <th>Codigo</th>
                        <th>Tipo</th>
                        <th class="text-center">Unidad</th>
                        <th class="text-right">Costo unit.</th>
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
                            <td class="text-xs">{{ $recipe->input->code }}</td>
                            <td class="text-xs">{{ $recipe->input->category ?? '—' }}</td>
                            <td class="text-center text-xs">{{ $recipe->input->unit }}</td>
                            <td class="text-right">${{ number_format((float) $recipe->input->unit_cost, 0, ',', '.') }}</td>
                            <td class="text-right font-bold">{{ $recipe->qty_per_box == floor($recipe->qty_per_box) ? number_format($recipe->qty_per_box, 0, ',', '.') : number_format($recipe->qty_per_box, 2, ',', '.') }}</td>
                            <td class="text-right font-bold">${{ number_format($lineCost, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="row-alt-bg">
                        <th colspan="6" class="text-right font-bold">Costo total/caja:</th>
                        <th class="text-right font-bold text-primary-brand">
                            ${{ number_format($totalCost, 0, ',', '.') }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="data-table-empty">
            <p>Sin ingredientes.</p>
        </div>
    @endif
@endif
