<x-erp-layout title="Salas y Locales" subtitle="Gestiona las salas o locales asociados a cada cliente.">
    <div class="page-header">
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
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stores as $store)
                        <tr>
                            <td class="font-bold">{{ $store->customer?->trade_name ?? $store->customer?->business_name }}</td>
                            <td class="text-xs">{{ $store->code }}</td>
                            <td>{{ $store->name }}</td>
                            <td>{{ $store->city ?? '—' }}</td>
                            <td>
                                <span class="badge @if($store->status) badge-success @else badge-danger @endif">
                                    {{ $store->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-modal" data-url="{{ route('salas.edit', $store) }}" data-title="Editar: {{ $store->name }}">Editar</button>
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

    @else
        <div class="table-container">
            <div class="data-table-empty">
                <p>No hay salas registradas.</p>
            </div>
        </div>
    @endif
</x-erp-layout>
