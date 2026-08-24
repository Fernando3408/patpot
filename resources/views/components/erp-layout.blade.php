@props(['title', 'subtitle' => null])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · PatPot ERP</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    {{-- Left Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}" class="sidebar-brand-link">
                <span class="sidebar-brand-mark">PP</span>
                <div class="sidebar-brand-text">
                    <strong>PatPot</strong>
                    <small>ERP operativo</small>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i data-lucide="home" class="sidebar-link-icon"></i> Dashboard
            </a>

            {{-- Inventario --}}
            <div class="sidebar-group {{ request()->is(['productos*', 'insumos*', 'recetas*']) ? 'open' : '' }}">
                <button class="sidebar-group-toggle" onclick="this.parentElement.classList.toggle('open')">
                    <span><i data-lucide="package" class="sidebar-link-icon"></i> Inventario</span>
                    <span class="sidebar-caret">▸</span>
                </button>
                <div class="sidebar-group-menu">
                    <a href="/productos" class="sidebar-link {{ request()->is('productos*') ? 'active' : '' }}">Productos</a>
                    <a href="/insumos" class="sidebar-link {{ request()->is('insumos*') ? 'active' : '' }}">Insumos</a>
                    <a href="/recetas" class="sidebar-link {{ request()->is('recetas*') ? 'active' : '' }}">Recetas</a>
                </div>
            </div>

            {{-- Compras --}}
            <div class="sidebar-group {{ request()->is(['compras*', 'proveedores*']) ? 'open' : '' }}">
                <button class="sidebar-group-toggle" onclick="this.parentElement.classList.toggle('open')">
                    <span><i data-lucide="shopping-cart" class="sidebar-link-icon"></i> Compras</span>
                    <span class="sidebar-caret">▸</span>
                </button>
                <div class="sidebar-group-menu">
                    <a href="/compras" class="sidebar-link {{ request()->is('compras*') ? 'active' : '' }}">Compras</a>
                    <a href="/proveedores" class="sidebar-link {{ request()->is('proveedores*') ? 'active' : '' }}">Proveedores</a>
                </div>
            </div>

            {{-- Producción --}}
            <div class="sidebar-group {{ request()->is('produccion*') ? 'open' : '' }}">
                <button class="sidebar-group-toggle" onclick="this.parentElement.classList.toggle('open')">
                    <span><i data-lucide="factory" class="sidebar-link-icon"></i> Producción</span>
                    <span class="sidebar-caret">▸</span>
                </button>
                <div class="sidebar-group-menu">
                    <a href="/produccion" class="sidebar-link {{ request()->is('produccion*') ? 'active' : '' }}">Producción</a>
                </div>
            </div>

            {{-- Ventas --}}
            <div class="sidebar-group {{ request()->is(['pedidos*', 'precios*', 'clientes*', 'salas*', 'retail*']) || request()->routeIs(['customers.*', 'salas.*']) ? 'open' : '' }}">
                <button class="sidebar-group-toggle" onclick="this.parentElement.classList.toggle('open')">
                    <span><i data-lucide="dollar-sign" class="sidebar-link-icon"></i> Ventas</span>
                    <span class="sidebar-caret">▸</span>
                </button>
                <div class="sidebar-group-menu">
                    <a href="/pedidos" class="sidebar-link {{ request()->is('pedidos*') ? 'active' : '' }}">Pedidos</a>
                    <a href="/precios" class="sidebar-link {{ request()->is('precios*') ? 'active' : '' }}">Precios</a>
                    <a href="{{ route('customers.index') }}" class="sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">Clientes</a>
                    <a href="{{ route('salas.index') }}" class="sidebar-link {{ request()->routeIs('salas.*') ? 'active' : '' }}">Salas</a>
                    <a href="/retail" class="sidebar-link {{ request()->is('retail*') ? 'active' : '' }}">Retail</a>
                </div>
            </div>

            {{-- Control --}}
            @php
                $isAdminControl = request()->routeIs(['movements.*', 'audit.*', 'admin.*']);
            @endphp
            <div class="sidebar-group {{ $isAdminControl || request()->routeIs('tasks.*') || request()->routeIs('admin.trash.*') ? 'open' : '' }}">
                <button class="sidebar-group-toggle" onclick="this.parentElement.classList.toggle('open')">
                    <span><i data-lucide="settings" class="sidebar-link-icon"></i> Control</span>
                    <span class="sidebar-caret">▸</span>
                </button>
                <div class="sidebar-group-menu">
                    <a href="{{ route('tasks.index') }}" class="sidebar-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">Tareas</a>
                    @if(auth()->check() && auth()->user()->canManage())
                    <a href="{{ route('admin.trash.index') }}" class="sidebar-link {{ request()->routeIs('admin.trash.*') ? 'active' : '' }}">Papelera</a>
                    @endif
                    @if(auth()->check() && auth()->user()->isAdmin())
                    <div class="sidebar-divider"></div>
                    <a href="{{ route('movements.index') }}" class="sidebar-link {{ request()->routeIs('movements.*') ? 'active' : '' }}">Movimientos</a>
                    <a href="{{ route('audit.index') }}" class="sidebar-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">Auditoría</a>
                    <a href="{{ route('admin.index') }}" class="sidebar-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">Administración</a>
                    @endif
                </div>
            </div>
        </nav>

        <div class="sidebar-footer">
            <span class="sidebar-user">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-logout">Cerrar sesión</button>
            </form>
        </div>
    </aside>

    {{-- Main Content Area --}}
    <div class="page-wrapper">
        {{-- Page Header --}}
        <header class="page-topbar">
            <div class="page-topbar-title">
                <h1>{{ $title }}</h1>
                @if ($subtitle)
                <p class="text-sm text-muted">{{ $subtitle }}</p>
                @endif
            </div>
            <div class="page-topbar-actions">
                @if(auth()->check() && auth()->user()->canManage() && !request()->routeIs('dashboard'))
                <a class="btn btn-outline-info btn-sm" href="{{ route('admin.trash.index') }}"><i data-lucide="trash-2" style="width:14px;height:14px;"></i> Papelera</a>
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
    </div>

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

    @php
        $flashType = '';
        $flashMessage = '';
        if (session('success')) {
            $flashType = 'success';
            $flashMessage = session('success');
        } elseif ($errors->any()) {
            $flashType = 'error';
            $flashMessage = implode('<br>', $errors->all());
        }
    @endphp
    <div id="flash-data" style="display:none" data-type="{{ $flashType }}" data-message="{!! $flashMessage !!}"></div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
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

        var flashEl = document.getElementById('flash-data');
        if (flashEl && flashEl.dataset.type) {
            Toast.fire({ icon: flashEl.dataset.type, title: flashEl.dataset.message });
        }
    </script>
    <script>
        let currentEditingRow = null;
        let originalRowHtml = null;

        document.addEventListener('DOMContentLoaded', function() {
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
                if (!td.dataset.field) return;
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
    <script>lucide.createIcons();</script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.data-table').each(function() {
                var hasActions = $(this).find('th:last').text().trim().toLowerCase().includes('accion');
                $(this).DataTable({
                    language: {
                        search: "Buscar:",
                        lengthMenu: "Mostrar _MENU_",
                        info: "Mostrando _START_ a _END_ de _TOTAL_",
                        infoEmpty: "Sin resultados",
                        infoFiltered: "(filtrado de _MAX_)",
                        paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" },
                        zeroRecords: "No hay registros"
                    },
                    pageLength: 10,
                    order: [],
                    columnDefs: hasActions ? [{ orderable: false, targets: -1 }] : [],
                    dom: '<"top"f>rt<"bottom"lip><"clear">'
                });
            });
        });
    </script>
</body>
</html>
