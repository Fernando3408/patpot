<x-erp-layout title="Administración" subtitle="Gestión de usuarios y permisos.">
    <div class="page-header">
        <div class="page-header-actions">
            <a href="{{ route('admin.users.create') }}" class="btn btn-outline-primary btn-sm">+ Crear usuario</a>
        </div>
    </div>

    <div class="admin-tabs"><button type="button" class="admin-tab is-active" data-panel="usersPanel">Usuarios del sistema</button><button type="button" class="admin-tab" data-panel="optionsPanel">Conceptos y categorías</button><button type="button" class="admin-tab" data-panel="themesPanel">Temas</button></div>
    <div id="usersPanel" class="table-container admin-users-table">
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

    <div id="themesPanel" class="form-card admin-options-panel is-hidden">
        <h2 class="card__title">Temas del ERP</h2>
        <p class="text-muted mt-2">Elige una paleta para personalizar la interfaz. Se guardará en este navegador.</p>
        <div class="theme-picker-grid">
            <button type="button" class="theme-choice" data-theme-choice="rose"><span class="theme-swatch theme-swatch--rose"></span><strong>Rosa empolvado</strong><small>Suave y elegante</small></button>
            <button type="button" class="theme-choice" data-theme-choice="lavender"><span class="theme-swatch theme-swatch--lavender"></span><strong>Lavanda</strong><small>Sereno y delicado</small></button>
            <button type="button" class="theme-choice" data-theme-choice="peach"><span class="theme-swatch theme-swatch--peach"></span><strong>Durazno</strong><small>Cálido y luminoso</small></button>
            <button type="button" class="theme-choice" data-theme-choice="mint"><span class="theme-swatch theme-swatch--mint"></span><strong>Menta</strong><small>Fresco y natural</small></button>
            <button type="button" class="theme-choice" data-theme-choice="berry"><span class="theme-swatch theme-swatch--berry"></span><strong>Frambuesa</strong><small>Intenso y femenino</small></button>
            <button type="button" class="theme-choice" data-theme-choice="champagne"><span class="theme-swatch theme-swatch--champagne"></span><strong>Champagne</strong><small>Neutro y sofisticado</small></button>
            <button type="button" class="theme-choice" data-theme-choice="default"><span class="theme-swatch theme-swatch--default"></span><strong>PatPot original</strong><small>Volver a los colores actuales</small></button>
        </div>
    </div>

    <div id="optionsPanel" class="form-card admin-options-panel is-hidden">
        <h2 class="card__title">Conceptos y categorías de gastos</h2>
        <form method="POST" action="{{ route('admin.expense-options.store') }}" class="form-grid mt-3">@csrf
            <select name="type" class="form-control"><option value="concept">Concepto de pedido</option><option value="category">Categoría de costo mensual</option></select>
            <input name="name" class="form-control" placeholder="Nuevo nombre" required><button class="btn btn-primary">Agregar opción</button>
        </form>
        <div class="admin-option-columns">@foreach(['concept' => 'Conceptos de pedidos', 'category' => 'Categorías de costos'] as $type => $label)<div class="admin-option-group"><h3>{{ $label }}</h3><div class="admin-option-list">@foreach($expenseOptions->where('type', $type) as $option)<div class="admin-option-item"><span>{{ $option->name }}</span><form method="POST" action="{{ route('admin.expense-options.toggle', $option) }}">@csrf<button class="btn btn-outline-{{ $option->active ? 'warning' : 'success' }} btn-sm">{{ $option->active ? 'Desactivar' : 'Activar' }}</button></form></div>@endforeach</div></div>@endforeach</div>
    </div>

    <script>
        document.querySelectorAll('.admin-tab').forEach(function(tab) {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.admin-tab').forEach(function(item) { item.classList.remove('is-active'); });
                 document.querySelectorAll('#usersPanel, #optionsPanel, #themesPanel').forEach(function(panel) { panel.classList.add('is-hidden'); });
                tab.classList.add('is-active');
                document.getElementById(tab.dataset.panel).classList.remove('is-hidden');
            });
        });
        document.querySelectorAll('[data-theme-choice]').forEach(function(button) {
            button.addEventListener('click', function() {
                var theme = button.dataset.themeChoice;
                if (theme === 'default') { delete document.documentElement.dataset.theme; localStorage.removeItem('patpot-theme'); }
                else { document.documentElement.dataset.theme = theme; localStorage.setItem('patpot-theme', theme); }
                document.querySelectorAll('[data-theme-choice]').forEach(function(item) { item.classList.toggle('is-selected', item === button); });
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Tema aplicado', showConfirmButton: false, timer: 1400 });
            });
        });
        var savedTheme = localStorage.getItem('patpot-theme') || 'default';
        if (savedTheme !== 'default') document.documentElement.dataset.theme = savedTheme;
        document.querySelector('[data-theme-choice="' + savedTheme + '"]')?.classList.add('is-selected');
    </script>

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
                        var statusTd = tr.children[3];
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
