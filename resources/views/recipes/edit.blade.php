@if(!request()->ajax())
<x-erp-layout title="Editar receta / BOM" subtitle="Configura la lista de materiales (BOM) y cantidades consumidas por cada caja terminada.">
    <div class="form-card">
        
        {{-- Ficha Informativa del Producto --}}
        <div class="mb-4 p-4" style="background-color: var(--bg-surface-secondary, #f8fafc); border-radius: 8px;">
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
            </div>
        </div>

        @if ($inputs->isNotEmpty())
            @php
                $recipeQuantities = $product->recipes->keyBy('input_id')->map->qty_per_box;
                $totalCost = 0;
            @endphp

            <p class="text-sm text-muted mb-4">
                Marca los insumos que consume una caja terminada e indica la cantidad requerida.
            </p>

            <form method="POST" action="/recetas/{{ $product->id }}">
                @csrf
                @method('PUT')

                <div class="table-container mb-4">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 80px;">Incluir</th>
                                <th>Insumo</th>
                                <th>Código</th>
                                <th class="text-center">Unidad</th>
                                <th class="text-right">Costo unitario</th>
                                <th class="text-right" style="width: 180px;">Cantidad por caja</th>
                                <th class="text-right">Costo por caja</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inputs as $input)
                                @php
                                    $rawQuantity = old("ingredients.{$input->id}.qty_per_box", $recipeQuantities->get($input->id));
                                    $isSelected = old("ingredients.{$input->id}.selected", $recipeQuantities->has($input->id));
                                    
                                    // Limpieza de cantidad a flotante para cálculos
                                    $quantity = is_numeric($rawQuantity) ? (float) $rawQuantity : null;
                                    $lineCost = $isSelected && $quantity ? $quantity * (float) $input->unit_cost : 0;
                                    $totalCost += $lineCost;
                                @endphp

                                <tr>
                                    <td class="text-center">
                                        <input
                                            type="hidden"
                                            name="ingredients[{{ $input->id }}][input_id]"
                                            value="{{ $input->id }}"
                                        >
                                        <input
                                            type="checkbox"
                                            name="ingredients[{{ $input->id }}][selected]"
                                            value="1"
                                            @checked($isSelected)
                                        >
                                    </td>
                                    <td class="font-bold">{{ $input->name }}</td>
                                    <td class="text-xs">{{ $input->code }}</td>
                                    <td class="text-center text-xs">{{ $input->unit }}</td>
                                    <td class="text-right">${{ number_format((float) $input->unit_cost, 0, ',', '.') }}</td>
                                    <td class="text-right">
                                        <input
                                            type="number"
                                            class="form-control text-right"
                                            step="0.01"
                                            min="0"
                                            name="ingredients[{{ $input->id }}][qty_per_box]"
                                            value="{{ $quantity !== null ? round($quantity, 2) : '' }}"
                                            placeholder="0.00"
                                        >
                                    </td>
                                    <td class="text-right font-bold">
                                        ${{ number_format($lineCost, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color: var(--bg-surface-secondary, #f8fafc);">
                                <th colspan="6" class="text-right font-bold">Costo estimado por caja:</th>
                                <th class="text-right font-bold text-lg" style="color: var(--primary-color, #2563eb);">
                                    ${{ number_format($totalCost, 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Botones de Acción --}}
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Guardar receta
                    </button>
                </div>
            </form>
        @else
            <div class="data-table-empty mb-4">
                <p>No hay insumos activos disponibles. Crea un insumo antes de configurar una receta.</p>
            </div>
            <div class="form-actions">
        @endif

    </div>

</x-erp-layout>
@endif