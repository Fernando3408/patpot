@props(['title', 'subtitle' => null])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · PatPot ERP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    {{-- Fixed Header --}}
    <header class="app-header">
        {{-- Top Bar --}}
        <div class="topbar">
            <div class="topbar-left">
                <a href="{{ route('dashboard') }}" class="topbar-brand">
                    <span class="topbar-brand-mark">PP</span>
                    <span class="topbar-brand-text">
                        <strong>PatPot</strong>
                        <small>ERP operativo</small>
                    </span>
                </a>
            </div>
            <div class="topbar-right">
                <span class="topbar-user">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">Cerrar sesión</button>
                </form>
            </div>
        </div>

    {{-- Horizontal Navigation --}}
    <nav class="nav-horizontal">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            Home
        </a>

        {{-- Inventario --}}
        <div class="nav-dropdown {{ request()->is(['productos*', 'insumos*', 'recetas*']) ? 'active' : '' }}">
            <button class="nav-dropdown-toggle">
                Inventario <span class="nav-caret">▾</span>
            </button>
            <div class="nav-dropdown-menu">
                <a href="/productos" class="{{ request()->is('productos*') ? 'active' : '' }}">📦 Productos</a>
                <a href="/insumos" class="{{ request()->is('insumos*') ? 'active' : '' }}">🧪 Insumos</a>
                <a href="/recetas" class="{{ request()->is('recetas*') ? 'active' : '' }}">📜 Recetas</a>
            </div>
        </div>

        {{-- Compras --}}
        <div class="nav-dropdown {{ request()->is(['compras*', 'proveedores*']) ? 'active' : '' }}">
            <button class="nav-dropdown-toggle">
                Compras <span class="nav-caret">▾</span>
            </button>
            <div class="nav-dropdown-menu">
                <a href="/compras" class="{{ request()->is('compras*') ? 'active' : '' }}">🛒 Compras</a>
                <a href="/proveedores" class="{{ request()->is('proveedores*') ? 'active' : '' }}">🚚 Proveedores</a>
            </div>
        </div>

        {{-- Producción --}}
        <div class="nav-dropdown {{ request()->is('produccion*') ? 'active' : '' }}">
            <button class="nav-dropdown-toggle">
                Producción <span class="nav-caret">▾</span>
            </button>
            <div class="nav-dropdown-menu">
                <a href="/produccion" class="{{ request()->is('produccion*') ? 'active' : '' }}">🏭 Producción</a>
            </div>
        </div>

        {{-- Ventas --}}
        <div class="nav-dropdown {{ request()->is(['pedidos*', 'precios*', 'clientes*', 'salas*', 'retail*']) || request()->routeIs(['customers.*', 'salas.*']) ? 'active' : '' }}">
            <button class="nav-dropdown-toggle">
                Ventas <span class="nav-caret">▾</span>
            </button>
            <div class="nav-dropdown-menu">
                <a href="/pedidos" class="{{ request()->is('pedidos*') ? 'active' : '' }}">📋 Pedidos</a>
                <a href="/precios" class="{{ request()->is('precios*') ? 'active' : '' }}">🏷️ Precios</a>
                <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">👥 Clientes</a>
                <a href="{{ route('salas.index') }}" class="{{ request()->routeIs('salas.*') ? 'active' : '' }}">🏢 Salas</a>
                <a href="/retail" class="{{ request()->is('retail*') ? 'active' : '' }}">🏬 Retail</a>
            </div>
        </div>

        {{-- Control --}}
        @php
            $isAdminControl = request()->routeIs(['movements.*', 'audit.*', 'admin.*']);
        @endphp
        <div class="nav-dropdown {{ $isAdminControl ? 'active' : '' }}">
            <button class="nav-dropdown-toggle">
                Control <span class="nav-caret">▾</span>
            </button>
            <div class="nav-dropdown-menu">
                <a href="{{ route('tasks.index') }}" class="{{ request()->routeIs('tasks.*') ? 'active' : '' }}">✅ Tareas</a>
                @if(auth()->check() && auth()->user()->canManage())
                <a href="{{ route('admin.trash.index') }}" class="{{ request()->routeIs('admin.trash.*') ? 'active' : '' }}">🗑️ Papelera</a>
                @endif
                @if(auth()->check() && auth()->user()->isAdmin())
                <div class="nav-dropdown-divider"></div>
                <a href="{{ route('movements.index') }}" class="{{ request()->routeIs('movements.*') ? 'active' : '' }}">📊 Movimientos</a>
                <a href="{{ route('audit.index') }}" class="{{ request()->routeIs('audit.*') ? 'active' : '' }}">📋 Auditoría</a>
                <a href="{{ route('admin.index') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">⚙️ Administración</a>
                @endif
            </div>
        </div>
    </nav>
    </header>

    {{-- Page Header --}}
    <header class="page-topbar">
        <div class="page-topbar-title">
            <h1>{{ $title }}</h1>
            @if ($subtitle)
            <p class="text-sm text-muted">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="page-topbar-actions">
            @if(auth()->check() && auth()->user()->canManage())
                <a class="btn btn-outline-info btn-sm" href="{{ route('admin.trash.index') }}">🗑️ Papelera</a>
            @endif
        </div>
    </header>

    {{-- Content --}}
    <main class="page-content">
        @if (session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif

        {{ $slot }}
    </main>

    {{-- Detail Modal --}}
    <div id="detailModal" class="modal-overlay" style="display:none;">
        <div class="modal-container" style="max-width:800px;">
            <div class="modal-header">
                <h3 id="detailModalTitle">Detalle</h3>
                <button class="modal-close" onclick="closeDetailModal()">&times;</button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <div class="modal-loading">Cargando...</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: true,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#df6403',
            timer: 6000,
            timerProgressBar: true,
            didOpen: function(toast) {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        @if(session('success'))
        Toast.fire({ icon: 'success', title: '{!! session("success") !!}' });
        @endif

        @if($errors->any())
        var errorMessages = {!! json_encode($errors->all()) !!};
        Toast.fire({ icon: 'error', title: errorMessages.join('<br>') });
        @endif
    </script>
    <script>
        let currentEditingRow = null;
        let originalRowHtml = null;

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.nav-dropdown-toggle').forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var dropdown = toggle.closest('.nav-dropdown');
                    var wasOpen = dropdown.classList.contains('open');
                    document.querySelectorAll('.nav-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
                    if (!wasOpen) dropdown.classList.add('open');
                });
            });
            document.addEventListener('click', function() {
                document.querySelectorAll('.nav-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
            });

            document.querySelectorAll('.btn-detail-modal').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    openDetailModal(this.dataset.url, this.dataset.title || 'Detalle');
                });
            });
        });

        function showInlineDetail(btn) {
            var modal = document.getElementById('detailModal');
            var body = document.getElementById('detailModalBody');
            document.getElementById('detailModalTitle').textContent = btn.dataset.title || 'Detalle';
            var template = btn.parentElement.querySelector('template');
            if (template) {
                body.innerHTML = '';
                body.appendChild(template.content.cloneNode(true));
            } else {
                body.innerHTML = '<p>No hay datos.</p>';
            }
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function openDetailModal(url, title) {
            var modal = document.getElementById('detailModal');
            var body = document.getElementById('detailModalBody');
            document.getElementById('detailModalTitle').textContent = title;
            body.innerHTML = '<div class="modal-loading">Cargando...</div>';
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            fetch(url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', cache: 'no-store' })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                body.innerHTML = html.trim();
            })
            .catch(function() { body.innerHTML = '<p>Error al cargar.</p>'; });
        }

        function closeDetailModal() {
            document.getElementById('detailModal').style.display = 'none';
            document.body.style.overflow = '';
        }

        document.getElementById('detailModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeDetailModal();
        });

        function enableInlineEdit(row) {
            cancelInlineEdit();
            currentEditingRow = row;
            originalRowHtml = row.innerHTML;
            row.classList.add('editing');
            var cells = row.querySelectorAll('td');
            cells.forEach(function(td) {
                if (td.querySelector('.actions-cell')) return;
                if (td.dataset.readonly === 'true') return;
                var val = td.dataset.value !== undefined ? td.dataset.value : td.textContent.trim();
                td.dataset.originalValue = val;
                if (td.dataset.type === 'select' && td.dataset.options) {
                    var opts = JSON.parse(td.dataset.options);
                    var sel = document.createElement('select');
                    sel.className = 'form-control';
                    sel.style.cssText = 'width:100%;padding:0.2rem 0.4rem;font-size:0.8rem;border-radius:4px;';
                    opts.forEach(function(o) {
                        var opt = document.createElement('option');
                        opt.value = o.value;
                        opt.textContent = o.label;
                        if (o.value == val || o.label == val) opt.selected = true;
                        sel.appendChild(opt);
                    });
                    td.textContent = '';
                    td.appendChild(sel);
                } else if (td.dataset.type === 'date') {
                    var dateInput = document.createElement('input');
                    dateInput.type = 'date';
                    dateInput.className = 'form-control';
                    var dateParts = val.split('/');
                    if (dateParts.length === 3) {
                        dateInput.value = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0];
                    } else {
                        dateInput.value = val;
                    }
                    dateInput.style.cssText = 'width:100%;padding:0.2rem 0.4rem;font-size:0.8rem;border-radius:4px;';
                    td.textContent = '';
                    td.appendChild(dateInput);
                } else {
                    var cleanVal = val;
                    if (td.dataset.cleanup === 'int') {
                        cleanVal = val.replace(/[^0-9\-]/g, '');
                    } else if (td.dataset.cleanup === 'currency') {
                        cleanVal = val.replace(/[^0-9.\-]/g, '');
                    } else {
                        cleanVal = val.replace(/\s*cajas\s*/i, '').replace(/^\$/, '').trim();
                    }
                    var input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'form-control';
                    input.value = cleanVal;
                    input.style.cssText = 'width:100%;padding:0.2rem 0.4rem;font-size:0.8rem;border-radius:4px;';
                    td.textContent = '';
                    td.appendChild(input);
                }
            });
            var actionsTd = row.querySelector('.actions-cell');
            if (actionsTd) {
                actionsTd.innerHTML = '<button type="button" class="btn btn-primary btn-sm" onclick="confirmInlineEdit(this)">Confirmar</button> <button type="button" class="btn btn-outline-warning btn-sm" onclick="cancelInlineEdit()">Cancelar</button>';
            }
        }

        function confirmInlineEdit(btn) {
            Swal.fire({
                title: '¿Guardar cambios?',
                text: 'Se actualizarán los datos de este registro.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#df6403',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var row = btn.closest('tr');
                    var url = row.dataset.updateUrl;
                    var cells = row.querySelectorAll('td');
                    var formData = new FormData();
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                    formData.append('_method', 'PUT');
                    cells.forEach(function(td) {
                        if (!td.dataset.field) return;
                        if (td.dataset.readonly === 'true') return;
                        var el = td.querySelector('input, select');
                        if (el) {
                            var val = el.value;
                            if (td.dataset.type === 'date') {
                                var origParts = (td.dataset.originalValue || '').split('/');
                                var origDate = origParts.length === 3 ? origParts[2] + '-' + origParts[1] + '-' + origParts[0] : td.dataset.originalValue;
                                if (val === origDate) return;
                            } else {
                                if (td.dataset.cleanup === 'int') {
                                    val = val.replace(/[^0-9\-]/g, '');
                                } else if (td.dataset.cleanup === 'currency') {
                                    val = val.replace(/[^0-9.\-]/g, '');
                                } else if (td.dataset.cleanup === 'decimal') {
                                    val = val.replace(/[^0-9.\-]/g, '');
                                }
                                var origClean = (td.dataset.originalValue || '').replace(/\s*cajas\s*/i, '').replace(/^\$/, '').trim();
                                if (val === origClean) return;
                            }
                            formData.append(td.dataset.field, val);
                        }
                    });
                    fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(json) {
                        if (json.errors) {
                            var msgs = Object.values(json.errors).flat().join('\n');
                            Swal.fire('Error', msgs, 'error');
                        } else {
                            Swal.fire('Guardado', 'Los cambios fueron guardados.', 'success');
                            setTimeout(function() { location.reload(); }, 800);
                        }
                    })
                    .catch(function() {
                        Swal.fire('Error', 'No se pudo guardar.', 'error');
                    });
                }
            });
        }

        function cancelInlineEdit() {
            if (currentEditingRow && originalRowHtml) {
                currentEditingRow.innerHTML = originalRowHtml;
                currentEditingRow.classList.remove('editing');
                currentEditingRow = null;
                originalRowHtml = null;
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDetailModal();
                cancelInlineEdit();
            }
        });
    </script>
</body>
</html>
