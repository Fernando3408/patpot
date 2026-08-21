@if(!request()->ajax())
<x-erp-layout title="Editar tarea" subtitle="Actualizar datos de la tarea.">

    <div class="card" style="max-width: 100%;">
        <div class="card__body">
            <form method="POST" action="{{ route('tasks.update', $task) }}">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Título *</label>
                        <input type="text" name="title" class="form-input" required value="{{ old('title', $task->title) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Responsable</label>
                        <input type="text" name="owner" class="form-input" value="{{ old('owner', $task->owner) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha límite *</label>
                        <input type="date" name="due_on" class="form-input" required value="{{ old('due_on', $task->due_on?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prioridad *</label>
                        <select name="priority" class="form-input" required>
                            @foreach(['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', 'urgent' => 'Urgente'] as $val => $label)
                                <option value="{{ $val }}" {{ old('priority', $task->priority) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Módulo</label>
                        <select name="module" class="form-input">
                            @foreach(['General', 'Pedidos', 'Producción', 'Insumos', 'Compras', 'Retail', 'Calidad', 'Finanzas'] as $mod)
                                <option value="{{ $mod }}" {{ old('module', $task->module) === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado *</label>
                        <select name="status" class="form-input" required>
                            @foreach(['pending' => 'Pendiente', 'in_progress' => 'En proceso', 'completed' => 'Completado'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $task->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Notas</label>
                        <textarea name="notes" class="form-input" rows="3">{{ old('notes', $task->notes) }}</textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-warning">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@if(!request()->ajax())
</x-erp-layout>
