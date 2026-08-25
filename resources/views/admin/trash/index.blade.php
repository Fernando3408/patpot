<x-erp-layout title="Papelera" subtitle="Registros eliminados. Puedes recuperar o eliminar definitivamente.">
    <div class="page-header">
    </div>

    <div class="mb-4">
        <input type="checkbox" id="selectAll"> <label for="selectAll">Seleccionar todo</label>
    </div>

    @php $modules = [
    'product' => ['items' => $deletedProducts, 'label' => 'Productos'],
    'input' => ['items' => $deletedInputs, 'label' => 'Insumos'],
    'customer' => ['items' => $deletedCustomers, 'label' => 'Clientes'],
    'supplier' => ['items' => $deletedSuppliers, 'label' => 'Proveedores'],
    'store' => ['items' => $deletedStores, 'label' => 'Salas'],
    'retail' => ['items' => $deletedRetails, 'label' => 'Retail'],
    ]; @endphp

    @foreach($modules as $key => $module)
    @if($module['items']->isNotEmpty())
    <h3 class="mt-6">{{ $module['label'] }}</h3>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="th-narrow"><input type="checkbox" class="group-check" data-group="{{ $key }}"></th>
                    <th>Nombre</th>
                    <th>Eliminado el</th>
                    <th>Eliminado por</th>
                    <th class="text-right"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($module['items'] as $item)
                <tr>
                    <td><input type="checkbox" class="trash-check" name="ids[]" value="{{ $key }}_{{ $item->id }}"></td>
                    <td class="font-bold">{{ $item->name ?? $item->trade_name ?? '—' }}</td>
                    <td class="text-xs text-muted">{{ $item->deleted_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td class="text-xs text-muted">{{ $item->deleted_by ? \App\Models\User::withTrashed()->find($item->deleted_by)?->name ?? '—' : '—' }}</td>
                    <td class="text-right">
                        <div class="actions-cell">
                            <form method="POST" action="{{ route('admin.trash.restore', ['entity' => $key, 'id' => $item->id]) }}" class="inline-form" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-outline-success btn-sm">Recuperar</button>
                            </form>
                            <form method="POST" action="{{ route('admin.trash.force-delete', ['entity' => $key, 'id' => $item->id]) }}" class="inline-form" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm btn-delete">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @endforeach

    @php
    $hasAny = $deletedProducts->count() || $deletedInputs->count() || $deletedCustomers->count() || $deletedSuppliers->count() || $deletedStores->count() || $deletedRetails->count();
    @endphp

    @if($hasAny)
    <div class="mt-6">
        <form method="POST" action="{{ route('admin.trash.restore-multiple') }}">
            @csrf
            <input type="hidden" name="selections" id="selectedItems" value="">
            <button type="submit" class="btn btn-outline-success btn-sm" onclick="gatherSelected()">Recuperar seleccionados</button>
        </form>
    </div>
    @endif

    @if(!$hasAny)
    <div class="data-table-empty">
        <p>No hay registros en la papelera.</p>
    </div>
    @endif

    <script>
        document.getElementById('selectAll').addEventListener('change', function() {
            document.querySelectorAll('.trash-check').forEach(function(cb) { cb.checked = document.getElementById('selectAll').checked; });
        });

        function gatherSelected() {
            var selected = [];
            document.querySelectorAll('.trash-check:checked').forEach(function(cb) { selected.push(cb.value); });
            document.getElementById('selectedItems').value = JSON.stringify(selected);
        }
    </script>
</x-erp-layout>
