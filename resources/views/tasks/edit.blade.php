<form method="POST" action="{{ route('tasks.update', $task) }}">
    @csrf
    @method('PUT')

    <h3 class="text-sm" style="margin-top:0;">Datos de la Tarea</h3>
    <div class="form-grid">
        <div class="form-group col-span-full">
            <label class="form-label">Título *</label>
            <input type="text" name="title" class="form-control" required value="{{ old('title', $task->title) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Responsable</label>
            <input type="text" name="owner" class="form-control" value="{{ old('owner', $task->owner) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Fecha límite *</label>
            <input type="date" name="due_on" class="form-control" required value="{{ old('due_on', $task->due_on?->format('Y-m-d')) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Prioridad *</label>
            <select name="priority" class="form-control" required>
                @foreach(['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', 'urgent' => 'Urgente'] as $val => $label)
                    <option value="{{ $val }}" {{ old('priority', $task->priority) === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Módulo</label>
            <select name="module" class="form-control">
                @foreach(['General', 'Pedidos', 'Produccion', 'Insumos', 'Compras', 'Retail', 'Calidad', 'Finanzas'] as $mod)
                    <option value="{{ $mod }}" {{ old('module', $task->module) === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Estado *</label>
            <select name="status" class="form-control" required>
                @foreach(['pending' => 'Pendiente', 'in_progress' => 'En proceso', 'completed' => 'Completado'] as $val => $label)
                    <option value="{{ $val }}" {{ old('status', $task->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-span-full">
            <label class="form-label">Notas</label>
            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $task->notes) }}</textarea>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
</form>
