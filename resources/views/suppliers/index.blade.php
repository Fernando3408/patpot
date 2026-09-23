<x-erp-layout title="Proveedores" subtitle="Gestiona proveedores, tiempos de entrega y condiciones de pago.">
    <div class="page-header">
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
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $supplier)
                        <tr>
                            <td class="font-bold">{{ $supplier->name }}</td>
                            <td class="text-xs">{{ $supplier->rut ?? '—' }}</td>
                            <td>
                                <strong>{{ $supplier->contact_name ?? '—' }}</strong>
                                @if ($supplier->email)
                                    <br><span class="text-xs text-muted">{{ $supplier->email }}</span>
                                @endif
                            </td>
                            <td class="text-xs text-center">{{ $supplier->lead_time_days }} días</td>
                            <td>
                                <span class="badge @if($supplier->status) badge-success @else badge-danger @endif">
                                    {{ $supplier->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-modal" data-url="{{ route('proveedores.edit', $supplier) }}" data-title="Editar: {{ $supplier->name }}">Editar</button>
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

    @else
        <div class="table-container">
            <div class="data-table-empty">
                <p>No hay proveedores registrados.</p>
            </div>
        </div>
    @endif
</x-erp-layout>
