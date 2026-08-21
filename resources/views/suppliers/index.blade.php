<x-erp-layout title="Proveedores" subtitle="Gestiona proveedores, tiempos de entrega y condiciones de pago.">
    <div class="page-header">
        <form method="GET" action="{{ route('proveedores.index') }}" class="search-form">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, RUT, contacto..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-success btn-sm">Buscar</button>
            @if(request('search'))
                <a href="{{ route('proveedores.index') }}" class="btn btn-outline-warning btn-sm">Limpiar</a>
            @endif
        </form>
        <div class="page-header-actions">
            <a href="{{ route('proveedores.create') }}" class="btn btn-outline-primary btn-sm">+ Nuevo proveedor</a>
        </div>
    </div>

    @if($suppliers->count() > 0)
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>RUT</th>
                        <th>Contacto</th>
                        <th class="text-center">Lead time</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $supplier)
                        <tr data-update-url="{{ route('proveedores.update', $supplier) }}">
                            <td data-field="name" class="font-bold">{{ $supplier->name }}</td>
                            <td data-field="rut" class="text-xs">{{ $supplier->rut ?? '—' }}</td>
                            <td data-field="contact_name" data-value="{{ $supplier->contact_name ?? '' }}">
                                <strong>{{ $supplier->contact_name ?? '—' }}</strong>
                                @if ($supplier->email)
                                    <br><span class="text-xs text-muted">{{ $supplier->email }}</span>
                                @endif
                            </td>
                            <td data-field="lead_time_days" data-value="{{ $supplier->lead_time_days }}" class="text-xs text-center">{{ $supplier->lead_time_days }} días</td>
                            <td data-field="status" data-type="select" data-options='[{"value":"1","label":"Activo"},{"value":"0","label":"Inactivo"}]'>
                                <span class="badge @if($supplier->status) badge-success @else badge-danger @endif">
                                    {{ $supplier->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-inline" onclick="enableInlineEdit(this.closest('tr'))">Editar</button>
                                    <button type="button" class="btn btn-outline-info btn-sm btn-detail-modal" data-url="{{ route('proveedores.show', $supplier) }}" data-title="Detalle: {{ $supplier->name }}">Ver detalle</button>
                                    @if(auth()->user()->canManage())
                                        <form method="POST" action="{{ route('proveedores.destroy', $supplier) }}" class="inline-form" style="display:inline;">
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
        <div class="mt-4">{{ $suppliers->links() }}</div>
    @else
        <div class="table-container">
            <div class="data-table-empty">
                <p>No hay proveedores registrados.</p>
            </div>
        </div>
    @endif
</x-erp-layout>
