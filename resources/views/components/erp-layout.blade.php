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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <div class="quick-menu-wrapper">
                    <button type="button" class="btn btn-primary btn-sm quick-menu-trigger" id="quickBtn">
                        <span class="plus-icon">+</span> Ingreso rápido
                    </button>
                    <div id="quickMenu" class="quick-menu-dropdown" style="display:none;">
                        <a href="/pedidos/create" class="quick-menu-item">Nuevo pedido</a>
                        <a href="/compras/create" class="quick-menu-item">Nueva compra</a>
                        <a href="/produccion/create" class="quick-menu-item">Nueva producción</a>
                        <a href="/retail/create" class="quick-menu-item">Actualizar retail</a>
                        <a href="/tareas/create" class="quick-menu-item">Nueva tarea</a>
                    </div>
                </div>
                @if(auth()->check() && auth()->user()->canManage() && !request()->routeIs('dashboard'))
                <a class="btn btn-outline-info btn-sm" href="{{ route('admin.trash.index') }}"><i data-lucide="trash-2" class="icon-sm"></i> Papelera</a>
                @endif
            </div>
        </header>

        {{-- Content --}}
        <main class="page-content">
            {{ $slot }}
        </main>
    </div>

    {{-- Detail Modal --}}
    <div id="detailModal" class="modal-overlay" style="display:none;">
        <div class="modal-container modal-container--wide">
            <div class="modal-header">
                <h3 id="detailModalTitle">Detalle</h3>
                <button class="modal-close" onclick="closeDetailModal()">&times;</button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <div class="modal-loading">Cargando...</div>
            </div>
        </div>
    </div>

    {{-- Attachment Modal --}}
    <div id="attachmentModal" class="modal-overlay" style="display:none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="attachmentModalTitle">Adjuntos</h3>
                <button class="modal-close" onclick="closeAttachmentModal()">&times;</button>
            </div>
            <div class="modal-body" id="attachmentModalBody"></div>
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
                if (td.dataset.readonly === 'true') return;
                var val = td.dataset.value !== undefined ? td.dataset.value : td.textContent.trim().split('\n')[0].trim();
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
                if (!actionsTd.dataset.actionsHtml) {
                    actionsTd.dataset.actionsHtml = actionsTd.innerHTML;
                }
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
                                if (td.dataset.cleanup === 'int') {
                                    origClean = origClean.replace(/[^0-9\-]/g, '');
                                } else if (td.dataset.cleanup === 'currency' || td.dataset.cleanup === 'decimal') {
                                    origClean = origClean.replace(/[^0-9.\-]/g, '');
                                }
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
                            cells.forEach(function(td) {
                                if (!td.dataset.field || td.dataset.readonly === 'true') return;
                                if (td.dataset.calculated === 'true') return;
                                var el = td.querySelector('input, select');
                                if (el && td.dataset.field) {
                                    var newVal = el.value;
                                    if (td.dataset.type === 'select') {
                                        var sel = td.querySelector('select');
                                        var optText = sel.options[sel.selectedIndex].text;
                                        td.innerHTML = '<span class="badge badge-' + (newVal === '1' || newVal === 'active' ? 'success' : 'secondary') + '">' + optText + '</span>';
                                    } else if (td.dataset.type === 'date') {
                                        var parts = newVal.split('-');
                                        td.innerHTML = parts.length === 3 ? parts[2] + '/' + parts[1] + '/' + parts[0] : newVal;
                                    } else if (td.dataset.cleanup === 'int') {
                                        var clean = newVal.replace(/[^0-9\-]/g, '');
                                        var num = parseInt(clean, 10);
                                        td.innerHTML = isNaN(num) ? clean : num.toLocaleString('es-CL');
                                    } else if (td.dataset.cleanup === 'currency') {
                                        var cleanCur = newVal.replace(/[^0-9.\-]/g, '');
                                        var numCur = parseFloat(cleanCur);
                                        td.innerHTML = isNaN(numCur) ? cleanCur : '$' + numCur.toLocaleString('es-CL');
                                    } else {
                                        td.textContent = newVal;
                                    }
                                }
                            });
                            if (typeof window.onInlineEditSuccess === 'function') {
                                window.onInlineEditSuccess(row, json);
                            }
                            var actionsTd = row.querySelector('.actions-cell');
                            if (actionsTd && actionsTd.dataset.actionsHtml) {
                                actionsTd.innerHTML = actionsTd.dataset.actionsHtml;
                            }
                            row.classList.remove('editing');
                            currentEditingRow = null;
                            originalRowHtml = null;
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Guardado', showConfirmButton: false, timer: 3000 });
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

        document.addEventListener('dblclick', function(e) {
            var td = e.target.closest('td[data-field]');
            if (td && td.dataset.readonly !== 'true') {
                var row = td.closest('tr');
                if (row && row.dataset.updateUrl) enableInlineEdit(row);
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDetailModal();
                cancelInlineEdit();
                var qm = document.getElementById('quickMenu');
                if (qm) qm.style.display = 'none';
            }
        });

        document.addEventListener('click', function(e) {
            var qm = document.getElementById('quickMenu');
            var btn = document.getElementById('quickBtn');
            if (qm && qm.style.display !== 'none' && !qm.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
                qm.style.display = 'none';
            }
        });

        var quickBtn = document.getElementById('quickBtn');
        if (quickBtn) {
            quickBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                var qm = document.getElementById('quickMenu');
                var isVisible = qm.style.display === 'block';
                qm.style.display = isVisible ? 'none' : 'block';
            });
        }
    </script>
    <script>lucide.createIcons();</script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        $.fn.dataTable.ext.type.detect.unshift(function (d) {
            return /[\$\€\£]?\s?[\d\.\,]+/.test(d.replace(/\s/g, '')) ? 'currency' : null;
        });

        $.fn.dataTable.ext.type.order['currency-pre'] = function (d) {
            return parseFloat(d.replace(/[^\d\-,]/g, '').replace(/\./g, '').replace(',', '.')) || 0;
        };

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

            var headerFilters = document.querySelector('.page-header-filters');
            var headerActions = document.querySelector('.page-header-actions');
            if (headerFilters || headerActions) {
                var filter = document.querySelector('.dataTables_filter');
                if (filter) {
                    var searchInput = filter.querySelector('label') || filter;

                    var container = document.createElement('div');
                    container.className = 'dataTables-filter-row';

                    var leftSide = document.createElement('div');
                    leftSide.className = 'dataTables-filter-left';
                    if (headerFilters) leftSide.appendChild(headerFilters);

                    var rightSide = document.createElement('div');
                    rightSide.className = 'dataTables-filter-right';
                    rightSide.appendChild(searchInput);
                    if (headerActions) rightSide.appendChild(headerActions);

                    container.appendChild(leftSide);
                    container.appendChild(rightSide);
                    filter.innerHTML = '';
                    filter.appendChild(container);
                }
            }
        });
    </script>
    <script>
        window._attSelectedFiles = [];
        window._attPreview = null;
        window._attActions = null;
        window._attModelClass = '';
        window._attModelId = 0;

        function renderAttPreview() {
            window._attPreview.innerHTML = '';
            window._attActions.style.display = window._attSelectedFiles.length > 0 ? 'block' : 'none';
            window._attSelectedFiles.forEach(function(f, i) {
                var item = document.createElement('div');
                item.className = 'attachment-item';
                var size = f.size < 1024 ? f.size + ' B' : f.size < 1048576 ? (f.size / 1024).toFixed(1) + ' KB' : (f.size / 1048576).toFixed(1) + ' MB';
                item.innerHTML = '<span class="attachment-name">' + f.name + '</span><span class="attachment-size">' + size + '</span><button type="button" class="btn btn-danger btn-sm" onclick="removeAttFile(' + i + ')">&times;</button>';
                window._attPreview.appendChild(item);
            });
        }

        function removeAttFile(i) {
            window._attSelectedFiles.splice(i, 1);
            renderAttPreview();
        }

        function openAttachmentModal(modelClass, modelId, title) {
            var modal = document.getElementById('attachmentModal');
            var modalTitle = document.getElementById('attachmentModalTitle');
            var body = document.getElementById('attachmentModalBody');
            modalTitle.textContent = title || 'Adjuntos';
            body.innerHTML = '<div class="modal-loading">Cargando...</div>';
            modal.style.display = 'flex';
            window._attSelectedFiles = [];
            window._attModelClass = modelClass;
            window._attModelId = modelId;

            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/adjuntos/lista?model_class=' + encodeURIComponent(modelClass) + '&model_id=' + modelId, {
                method: 'GET',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            })
                .then(function(r) { return r.json(); })
                .then(function(data) { renderAttModalBody(data.attachments || []); })
                .catch(function() { renderAttModalBody([]); });
        }

        function renderAttModalBody(attachments) {
            var mc = window._attModelClass;
            var mid = window._attModelId;
            var html = '<div id="att-list">';
            if (attachments.length > 0) {
                attachments.forEach(function(att) {
                    html += '<div class="attachment-item" id="att-' + att.id + '">';
                    html += '<a href="/adjuntos/' + att.id + '/descargar" class="attachment-name">' + att.original_name + '</a>';
                    html += '<span class="attachment-size">' + att.formatted_size + '</span>';
                    html += '<button type="button" class="btn btn-danger btn-sm" onclick="deleteAttachment(' + att.id + ')" title="Eliminar">&times;</button>';
                    html += '</div>';
                });
            } else {
                html += '<div class="attachment-empty">No hay archivos adjuntos.</div>';
            }
            html += '</div>';
            html += '<hr style="margin:12px 0;border-color:var(--border);">';
            html += '<form id="att-upload-form" enctype="multipart/form-data" onsubmit="return false;">';
            html += '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').getAttribute('content') + '">';
            html += '<input type="hidden" name="model_class" value="' + mc + '">';
            html += '<input type="hidden" name="model_id" value="' + mid + '">';
            html += '<input type="file" name="files[]" id="att-modal-input" multiple style="display:none;" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv">';
            html += '<button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById(\'att-modal-input\').click();">+ Agregar archivo</button>';
            html += '<div id="att-modal-preview" style="margin-top:8px;"></div>';
            html += '<div class="mt-3" id="att-modal-actions" style="display:none;text-align:right;">';
            html += '<button type="button" class="btn btn-primary btn-sm" onclick="uploadAttachments()">Subir archivos</button>';
            html += '</div>';
            html += '</form>';
            document.getElementById('attachmentModalBody').innerHTML = html;

            window._attPreview = document.getElementById('att-modal-preview');
            window._attActions = document.getElementById('att-modal-actions');

            document.getElementById('att-modal-input').addEventListener('change', function(e) {
                Array.from(e.target.files).forEach(function(f) {
                    if (f.size > 10 * 1024 * 1024) {
                        Swal.fire('Archivo muy grande', f.name + ' supera los 10 MB.', 'warning');
                        return;
                    }
                    window._attSelectedFiles.push(f);
                });
                renderAttPreview();
            });
        }

        function closeAttachmentModal() {
            document.getElementById('attachmentModal').style.display = 'none';
        }

        function deleteAttachment(id) {
            Swal.fire({
                title: 'Eliminar archivo',
                text: '¿Seguro que deseas eliminar este archivo?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#df6403',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/adjuntos/' + id;
                    form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').getAttribute('content') + '"><input type="hidden" name="_method" value="DELETE">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function uploadAttachments() {
            if (!window._attSelectedFiles.length) return;
            var form = document.getElementById('att-upload-form');
            var formData = new FormData(form);
            var btn = document.querySelector('#att-modal-actions .btn-primary');
            btn.disabled = true;
            btn.textContent = 'Subiendo...';
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/adjuntos', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.onload = function() {
                btn.disabled = false;
                btn.textContent = 'Subir archivos';
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.ok) {
                        window._attSelectedFiles = [];
                        openAttachmentModal(window._attModelClass, window._attModelId, document.getElementById('attachmentModalTitle').textContent);
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Archivos subidos', timer: 2000, showConfirmButton: false });
                    } else {
                        Swal.fire('Error', data.message || 'No se pudieron subir los archivos.', 'error');
                    }
                } catch(e) {
                    Swal.fire('Error', 'Respuesta inesperada del servidor.', 'error');
                }
            };
            xhr.onerror = function() {
                btn.disabled = false;
                btn.textContent = 'Subir archivos';
                Swal.fire('Error', 'Error de conexión.', 'error');
            };
            xhr.send(formData);
        }
    </script>
</body>
</html>
