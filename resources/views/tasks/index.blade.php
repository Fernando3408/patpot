<x-erp-layout title="Tareas" subtitle="Pendientes operacionales conectados con compras, pedidos, producción y retail.">
    <div class="page-header">
        <form method="GET" action="{{ route('tasks.index') }}" class="search-form">
            <input type="text" name="search" class="form-control" placeholder="Buscar por título, responsable..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-success btn-sm">Buscar</button>
            @if(request('search'))
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-warning btn-sm">Limpiar</a>
            @endif
        </form>
        <div class="page-header-actions">
            <a href="{{ route('tasks.create') }}" class="btn btn-outline-primary btn-sm">＋ Nueva tarea</a>
        </div>
    </div>

    @if($tasks->count() > 0)
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tarea</th>
                        <th>Responsable</th>
                        <th>Fecha límite</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        <tr style="{{ $task->is_overdue ? 'background-color: #fef2f2;' : '' }}" data-update-url="{{ route('tasks.update', $task) }}">
                            <td data-field="title" data-value="{{ $task->title }}">
                                <strong>{{ $task->title }}</strong>
                                @if($task->notes)
                                    <br><span class="text-xs text-muted">{{ Str::limit($task->notes, 60) }}</span>
                                @endif
                            </td>
                            <td data-field="owner" class="text-xs">{{ $task->owner ?? '—' }}</td>
                            <td data-field="due_on" class="text-xs">{{ $task->due_on?->format('d-m-Y') ?? '—' }}</td>
                            <td data-field="priority" data-type="select" data-options='[{"value":"urgent","label":"Urgente"},{"value":"high","label":"Alta"},{"value":"medium","label":"Media"},{"value":"low","label":"Baja"}]'>
                                @php
                                    $priorityBadge = match($task->priority) {
                                        'urgent' => 'badge-danger',
                                        'high' => 'badge-warning',
                                        'medium' => 'badge-info',
                                        default => 'badge-success',
                                    };
                                    $priorityLabel = match($task->priority) {
                                        'urgent' => 'Urgente',
                                        'high' => 'Alta',
                                        'medium' => 'Media',
                                        default => 'Baja',
                                    };
                                @endphp
                                <span class="badge {{ $priorityBadge }}">{{ $priorityLabel }}</span>
                            </td>
                            <td data-field="status" data-type="select" data-options='[{"value":"pending","label":"Pendiente"},{"value":"in_progress","label":"En proceso"},{"value":"completed","label":"Completado"}]'>
                                @php
                                    $statusBadge = match($task->status) {
                                        'completed' => 'badge-success',
                                        'in_progress' => 'badge-info',
                                        default => 'badge-warning',
                                    };
                                    $statusLabel = match($task->status) {
                                        'completed' => 'Completado',
                                        'in_progress' => 'En proceso',
                                        default => 'Pendiente',
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="text-right">
                                <div class="actions-cell">
                                    @if($task->status !== 'completed')
                                        <form method="POST" action="{{ route('tasks.complete', $task) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">Completar</button>
                                        </form>
                                    @endif
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-inline" onclick="enableInlineEdit(this.closest('tr'))">Editar</button>
                                    @if(auth()->user()->canManage())
                                        <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="inline-form" style="display:inline;">
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
                <p>No hay tareas registradas.</p>
            </div>
        </div>
    @endif
</x-erp-layout>
