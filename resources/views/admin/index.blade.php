<x-erp-layout title="Administración" subtitle="Gestión de usuarios y permisos.">
    <div class="page-header">
        <div class="page-header-actions">
            <a href="{{ route('admin.users.create') }}" class="btn btn-outline-primary btn-sm">+ Crear usuario</a>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th class="text-right"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td class="font-bold">{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->roles->pluck('name')->implode(', ') ?: 'Sin rol' }}</td>
                        <td>
                            <span class="badge {{ $u->status ? 'badge-success' : 'badge-danger' }}">
                                {{ $u->status ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="actions-cell">
                                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-outline-success btn-sm btn-edit-modal" data-url="{{ route('admin.users.edit', $u) }}" data-title="Editar usuario">Editar</a>
                                @if($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $u) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-{{ $u->status ? 'warning' : 'success' }} btn-sm">
                                            {{ $u->status ? 'Deshabilitar' : 'Habilitar' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" style="display:inline;">
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
</x-erp-layout>
