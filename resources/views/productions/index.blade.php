<x-erp-layout title="Producción" subtitle="Planifica la fabricación y cierra cada orden con movimientos automáticos de stock.">
    <div class="page-header">
        <div class="page-header-filters">
            <form method="GET" action="{{ route('produccion.index') }}" class="search-form">
                <input type="date" name="from" class="form-control" value="{{ request('from') }}" placeholder="Desde">
                <input type="date" name="to" class="form-control" value="{{ request('to') }}" placeholder="Hasta">
                <select name="status" class="form-control">
                    <option value="">Todos los estados</option>
                    <option value="planned" {{ request('status') === 'planned' ? 'selected' : '' }}>Planificada</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En proceso</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Cerrada</option>
                </select>
                <button type="submit" class="btn btn-outline-success btn-sm">Filtrar</button>
                @if(request()->hasAny(['from', 'to', 'status']))
                    <a href="{{ route('produccion.index') }}" class="btn btn-outline-warning btn-sm">Limpiar</a>
                @endif
            </form>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('produccion.create') }}" class="btn btn-outline-primary btn-sm">+ Nueva producción</a>
        </div>
    </div>

    @if($productions->count() > 0)
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Producto</th>
                        <th>Plan / Real</th>
                        <th>Estado</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productions as $production)
                        <tr data-update-url="{{ route('produccion.update', $production) }}">
                            <td>
                                <div class="font-bold">{{ $production->number }}</div>
                                <div class="text-xs text-muted">{{ $production->planned_on->format('d-m-Y') }}</div>
                            </td>
                            <td>{{ $production->product->name }}</td>
                            <td>
                                {{ number_format($production->planned_boxes, 0, ',', '.') }} /
                                @if($production->actual_boxes !== null)
                                    {{ number_format($production->actual_boxes, 0, ',', '.') }}
                                @else
                                    —
                                @endif
                                cajas
                            </td>
                            <td data-field="status" data-type="select" data-options='[{"value":"planned","label":"Planificada"},{"value":"in_progress","label":"En proceso"},{"value":"closed","label":"Cerrada"}]'>
                                @php
                                    $statusBadge = match($production->status) {
                                        'closed' => 'badge-success',
                                        'in_progress' => 'badge-warning',
                                        default => 'badge-info',
                                    };
                                    $statusLabel = match($production->status) {
                                        'closed' => 'Cerrada',
                                        'in_progress' => 'En proceso',
                                        default => 'Planificada',
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    <button type="button" class="btn btn-outline-info btn-sm btn-detail-modal" data-url="{{ route('productions.show', $production) }}" data-title="Detalle: {{ $production->number }}">Ver detalle</button>
                                    @if($production->status !== 'closed')
                                        <button type="button" class="btn btn-outline-success btn-sm btn-edit-inline" onclick="enableInlineEdit(this.closest('tr'))">Editar</button>

                                        @if(auth()->user()->canManage())
                                            <form method="POST" action="{{ route('produccion.destroy', $production) }}" class="inline-form" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm btn-delete">Eliminar</button>
                                            </form>
                                        @endif

                                        <details class="d-inline-block">
                                            <summary class="btn btn-primary btn-sm">Cerrar</summary>
                                            <div class="mt-2 p-3 bg-light border rounded tooltip-box">
                                                <form method="POST" action="{{ route('productions.close', $production) }}">
                                                    @csrf
                                                    <div class="form-group mb-2">
                                                        <label class="text-xs">Cajas reales</label>
                                                        <input type="number" step="1" name="actual_boxes" class="form-control form-control-sm" value="{{ $production->planned_boxes }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="text-xs">Fecha completada</label>
                                                        <input type="date" name="completed_on" class="form-control form-control-sm" value="{{ today()->format('Y-m-d') }}">
                                                    </div>
                                                    <button type="submit" class="btn btn-primary btn-sm w-100">Cerrar producción</button>
                                                </form>
                                            </div>
                                        </details>
                                    @else
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
                <p>No hay producciones registradas.</p>
            </div>
        </div>
    @endif
</x-erp-layout>
