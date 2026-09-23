<x-erp-layout title="Tareas" subtitle="Pendientes operacionales conectados con compras, pedidos, producción y retail.">
    <div class="page-header">
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
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        <tr class="{{ $task->is_overdue ? 'row-overdue' : '' }}">
                            <td>
                                <strong>{{ $task->title }}</strong>
                                @if($task->notes)
                                    <br><span class="text-xs text-muted">{{ Str::limit($task->notes, 60) }}</span>
                                @endif
                            </td>
                            <td class="text-xs">{{ $task->owner ?? '—' }}</td>
                            <td class="text-xs">{{ $task->due_on?->format('d-m-Y') ?? '—' }}</td>
                            <td>
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
                            <td>
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
                                        <form method="POST" action="{{ route('tasks.complete', $task) }}" class="complete-task-form" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">Completar</button>
                                        </form>
                                    @endif
                                    <button type="button" class="btn btn-outline-success btn-sm btn-edit-modal" data-url="{{ route('tasks.edit', $task) }}" data-title="Editar: {{ $task->title }}">Editar</button>
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

    <script>
        document.querySelectorAll('.complete-task-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(r) { return r.json(); })
                .then(function(json) {
                    if (json.success) {
                        var tr = form.closest('tr');
                        var statusTd = tr.children[4];
                        statusTd.innerHTML = '<span class="badge badge-success">Completada</span>';
                        form.remove();
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Tarea completada', showConfirmButton: false, timer: 2000 });
                    }
                })
                .catch(function() { Swal.fire('Error', 'No se pudo completar.', 'error'); });
            });
        });
    </script>
</x-erp-layout>
