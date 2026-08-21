<x-erp-layout title="Administración" subtitle="Gestión de usuarios y permisos.">
    <div class="page-header">
        <form method="GET" action="{{ route('admin.index') }}" class="search-form">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o email..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-success btn-sm">Buscar</button>
            @if(request('search'))
                <a href="{{ route('admin.index') }}" class="btn btn-outline-warning btn-sm">Limpiar</a>
            @endif
        </form>
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
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->roles->pluck('name')->implode(', ') ?: 'Sin rol' }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-outline-success btn-sm btn-edit-modal" data-url="{{ route('admin.users.edit', $u) }}" data-title="Editar usuario">Editar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-erp-layout>