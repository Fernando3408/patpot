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
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $u) }}" class="toggle-status-form" style="display:inline;">
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

    <script>
        document.querySelectorAll('.toggle-status-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
                .then(function(res) {
                    if (res.ok && res.data.success) {
                        var tr = form.closest('tr');
                        var statusTd = tr.children[2];
                        statusTd.innerHTML = res.data.status ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>';
                        var btn = form.querySelector('button');
                        if (res.data.status) {
                            btn.className = 'btn btn-outline-warning btn-sm';
                            btn.textContent = 'Deshabilitar';
                        } else {
                            btn.className = 'btn btn-outline-success btn-sm';
                            btn.textContent = 'Habilitar';
                        }
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Estado actualizado', showConfirmButton: false, timer: 2000 });
                    } else {
                        Swal.fire('Error', res.data.errors?.error?.[0] || 'No se pudo cambiar el estado.', 'error');
                    }
                })
                .catch(function() { Swal.fire('Error', 'No se pudo cambiar el estado.', 'error'); });
            });
        });
    </script>
</x-erp-layout>
