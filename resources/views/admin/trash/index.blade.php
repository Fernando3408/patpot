<x-erp-layout title="Papelera" subtitle="Registros eliminados. Puedes recuperar o eliminar definitivamente.">
    <div class="page-header">
        <form method="GET" action="{{ route('admin.trash.index') }}" class="search-form">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-success btn-sm">Buscar</button>
            @if(request('search'))
                <a href="{{ route('admin.trash.index') }}" class="btn btn-outline-warning btn-sm">Limpiar</a>
            @endif
        </form>
        <div class="page-header-actions">
            <button type="button" class="btn btn-outline-success btn-sm" onclick="restoreSelected()">Recuperar seleccionados</button>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteSelected()">Eliminar definitivamente</button>
        </div>
    </div>

    <div style="margin-bottom:1rem;">
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
    <h3 style="margin-top:1.5rem;">{{ $module['label'] }}</h3>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40px;"><input type="checkbox" class="group-check" data-group="{{ $key }}"></th>
                    <th>Nombre</th>
                    <th>Eliminado el</th>
                    <th>Eliminado por</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($module['items'] as $item)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="trash-check" data-group="{{ $key }}"></td>
                    <td>{{ $item->name ?? $item->business_name ?? $item->code ?? '—' }}</td>
                    <td>{{ $item->deleted_at->format('d-m-Y H:i') }}</td>
                    <td>{{ $item->deleter?->name ?? '—' }}</td>
                    <td class="text-right">
                        <form method="POST" action="{{ route('admin.trash.restore') }}" style="display:inline;">
                            @csrf
                            <input type="hidden" name="model" value="{{ $key }}">
                            <input type="hidden" name="id" value="{{ $item->id }}">
                            <button class="btn btn-outline-success btn-sm">Recuperar</button>
                        </form>
                        <form method="POST" action="{{ route('admin.trash.force-delete') }}" style="display:inline;" onsubmit="return confirm('¿Eliminar permanentemente?')">
                            @csrf
                            <input type="hidden" name="model" value="{{ $key }}">
                            <input type="hidden" name="id" value="{{ $item->id }}">
                            <button class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @endforeach

    @php $allEmpty = true; @endphp
    @foreach($modules as $module)
    @if($module['items']->isNotEmpty())
    @php $allEmpty = false; @endphp
    @endif
    @endforeach
    @if($allEmpty)
    <div class="data-table-empty">
        <p>La papelera está vacía.</p>
    </div>
    @endif

    <script>
        document.getElementById('selectAll')?.addEventListener('change', function() {
            document.querySelectorAll('.trash-check').forEach(c => c.checked = this.checked);
        });

        document.querySelectorAll('.group-check').forEach(g => {
            g.addEventListener('change', function() {
                document.querySelectorAll('.trash-check[data-group="' + this.dataset.group + '"]').forEach(c => c.checked = this.checked);
            });
        });

        function restoreSelected() {
            submitMultiple('{{ route("admin.trash.restore-multiple") }}');
        }

        function deleteSelected() {
            if (!confirm('¿Eliminar permanentemente los seleccionados?')) return;
            submitMultiple('{{ route("admin.trash.force-delete-multiple") }}');
        }

        function submitMultiple(url) {
            var checked = document.querySelectorAll('.trash-check:checked');
            if (!checked.length) {
                alert('Selecciona al menos uno.');
                return;
            }
            var model = checked[0].dataset.group;
            var tmp = document.createElement('form');
            tmp.method = 'POST';
            tmp.action = url;
            var tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            tmp.appendChild(tokenInput);
            var modelInput = document.createElement('input');
            modelInput.type = 'hidden';
            modelInput.name = 'model';
            modelInput.value = model;
            tmp.appendChild(modelInput);
            checked.forEach(function(c) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = c.value;
                tmp.appendChild(input);
            });
            document.body.appendChild(tmp);
            tmp.submit();
        }
    </script>
</x-erp-layout>
