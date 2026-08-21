<x-erp-layout title="Salas y Locales" subtitle="Gestiona las salas o locales asociados a cada cliente.">
    <div class="page-header">
        <form method="GET" action="{{ route('salas.index') }}" class="search-form">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, código, ciudad..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-success btn-sm">Buscar</button>
            @if(request('search'))
                <a href="{{ route('salas.index') }}" class="btn btn-outline-warning btn-sm">Limpiar</a>
            @endif
        </form>
        <div class="page-header-actions">
            <a href="{{ route('salas.create') }}" class="btn btn-outline-primary btn-sm">+ Nueva sala</a>
        </div>
    </div>

    @if($stores->count() > 0)
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Ciudad</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stores as $store)
                        <tr data-update-url="{{ route('salas.update', $store) }}">
                            <td class="font-bold">{{ $store->customer?->trade_name ?? $store->customer?->business_name }}</td>
                            <td data-field="code" class="text-xs">{{ $store->code }}</td>
                            <td data-field="name">{{ $store->name }}</td>
                            <td data-field="city">{{ $store->city ?? '—' }}</td>
                            <td data-field="status" data-type="select" data-options='[{"value":"1","label":"Activo"},{"value":"0","label":"Inactivo"}]'>
                                <span class="badge @if($store->status) badge-success @else badge-danger @endif">
                                    {{ $store->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-inline" onclick="enableInlineEdit(this.closest('tr'))">Editar</button>
                                    <button type="button" class="btn btn-outline-info btn-sm btn-detail-modal" data-url="{{ route('salas.show', $store) }}" data-title="Detalle: {{ $store->name }}">Ver detalle</button>
                                    @if(auth()->user()->canManage())
                                        <form method="POST" action="{{ route('salas.destroy', $store) }}" class="inline-form" style="display:inline;">
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
        <div class="mt-4">{{ $stores->links() }}</div>
    @else
        <div class="table-container">
            <div class="data-table-empty">
                <p>No hay salas registradas.</p>
            </div>
        </div>
    @endif
</x-erp-layout>
