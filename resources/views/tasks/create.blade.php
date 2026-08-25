<x-erp-layout title="Nueva tarea" subtitle="Registrar una nueva tarea operacional.">

    <div class="card card--full">
        <div class="card__body">
            <form method="POST" action="{{ route('tasks.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group col-span-full">
                        <label class="form-label">Titulo *</label>
                        <input type="text" name="title" class="form-input" required value="{{ old('title') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Responsable</label>
                        <input type="text" name="owner" class="form-input" value="{{ old('owner', 'Administracion') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha limite *</label>
                        <input type="date" name="due_on" class="form-input" required value="{{ old('due_on', now()->addDays(3)->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prioridad *</label>
                        <select name="priority" class="form-input" required>
                            <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Baja</option>
                            <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Media</option>
                            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>Alta</option>
                            <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgente</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Modulo</label>
                        <select name="module" class="form-input">
                            @foreach(['General', 'Pedidos', 'Produccion', 'Insumos', 'Compras', 'Retail', 'Calidad', 'Finanzas'] as $mod)
                                <option value="{{ $mod }}" {{ old('module') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado *</label>
                        <select name="status" class="form-input" required>
                            <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>En proceso</option>
                            <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completado</option>
                        </select>
                    </div>
                    <div class="form-group col-span-full">
                        <label class="form-label">Notas</label>
                        <textarea name="notes" class="form-input" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-warning">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</x-erp-layout>
