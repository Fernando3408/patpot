@php
    $isPartial = request()->ajax() || request()->has('_partial');
@endphp
@if(!$isPartial)
<x-erp-layout title="Editar receta / BOM" subtitle="Configura la lista de materiales (BOM) y cantidades consumidas por cada caja terminada.">
@endif
    <div class="form-card recipe-editor-card">
        
        {{-- Ficha Informativa del Producto --}}
        <div class="mb-4 p-4 section-alt-bg recipe-product-summary">
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

                <div class="table-container mb-4 recipe-components-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="text-center th-narrow">Incluir</th>
                                <th>Insumo</th>
                                <th>Codigo</th>
                                <th class="text-center">Unidad</th>
                                <th class="text-right">Costo unitario</th>
                                <th class="text-right th-progreso">Cantidad por caja</th>
                                <th class="text-right">Costo por caja</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inputs as $input)
                                @php
                                    $rawQuantity = old("ingredients.{$input->id}.qty_per_box", $recipeQuantities->get($input->id));
                                    $isSelected = old("ingredients.{$input->id}.selected", $recipeQuantities->has($input->id));
                                    $fixedCost = old("ingredients.{$input->id}.fixed_cost", $product->recipes->firstWhere('input_id', $input->id)?->fixed_cost);
                                    
                                    $quantity = is_numeric($rawQuantity) ? (float) $rawQuantity : null;
                                    $lineCost = $isSelected ? ($input->isService() ? (float) ($fixedCost ?: 0) : ($quantity ? $quantity * (float) $input->unit_cost : 0)) : 0;
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
                                    <td class="text-right">
                                        @if($input->isService())
                                            <input type="number" step="0.01" min="0" name="ingredients[{{ $input->id }}][fixed_cost]" class="form-control text-right" value="{{ $fixedCost !== null ? round((float) $fixedCost, 2) : '' }}" placeholder="$ por caja">
                                        @else
                                            ${{ number_format((float) $input->unit_cost, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <input
                                            type="number"
                                            class="form-control text-right"
                                            step="0.001"
                                            min="0"
                                            name="ingredients[{{ $input->id }}][qty_per_box]"
                                            value="{{ $quantity !== null ? round($quantity, 3) : '' }}"
                                            placeholder="0.000"
                                        >
                                    </td>
                                    <td class="text-right font-bold">
                                        ${{ number_format($lineCost, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="row-alt-bg">
                                <th colspan="6" class="text-right font-bold">Costo estimado por caja:</th>
                        <th class="text-right font-bold text-lg text-primary-brand"><span id="recipe-cost-net">${{ number_format($totalCost, 2, ',', '.') }}</span></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="recipe-cost-reference"><div><span>Costo sin IVA</span><strong id="recipe-cost-net-reference">$0,00</strong></div><div><span>IVA 19%</span><strong id="recipe-cost-vat">$0,00</strong></div><div><span>Costo con IVA</span><strong id="recipe-cost-gross">$0,00</strong></div><div><span>Costo unitario referencial</span><strong id="recipe-cost-unit">$0,00</strong></div></div>

                {{-- Botones de Accion --}}
                <div class="form-actions recipe-actions">
                    <button type="submit" class="btn btn-primary">
                        Guardar receta
                    </button>
                </div>
            </form>
        @else
            <div class="data-table-empty mb-4">
                <p>No hay insumos activos disponibles. Crea un insumo antes de configurar una receta.</p>
            </div>
        @endif

    </div>

    <script>
        (function () {
            const vat = 0.19;
            const units = {{ (int) $product->units_per_box }};
            const money = value => '$' + value.toLocaleString('es-CL', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            function updateRecipeReference() {
                let total = 0;
                document.querySelectorAll('.recipe-components-table tbody tr').forEach(function (row) {
                    const checkbox = row.querySelector('input[type="checkbox"]');
                    const qty = parseFloat(row.querySelector('input[name*="[qty_per_box]"]')?.value || 0);
                    const fixed = parseFloat(row.querySelector('input[name*="[fixed_cost]"]')?.value || 0);
                    const unit = parseFloat(row.children[4]?.textContent.replace(/[^0-9,.-]/g, '').replace('.', '').replace(',', '.') || 0);
                    if (checkbox?.checked) total += fixed || qty * unit;
                });
                document.getElementById('recipe-cost-net').textContent = money(total);
                document.getElementById('recipe-cost-net-reference').textContent = money(total);
                document.getElementById('recipe-cost-vat').textContent = money(total * vat);
                document.getElementById('recipe-cost-gross').textContent = money(total * (1 + vat));
                document.getElementById('recipe-cost-unit').textContent = money(units > 0 ? total / units : 0);
            }
            document.querySelectorAll('.recipe-components-table input').forEach(input => input.addEventListener('input', updateRecipeReference));
            updateRecipeReference();
        })();
    </script>

@if(!$isPartial)
</x-erp-layout>
@endif
