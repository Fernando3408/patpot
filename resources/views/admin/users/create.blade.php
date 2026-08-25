<x-erp-layout title="Crear Usuario">
    <div class="card">
        <div class="card__header">
            <h1 class="card__title">Nuevo usuario</h1>
        </div>
        <div class="card__body">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-input" value="{{ old('name') }}" required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-input" required>
                        <small class="form-hint">Mínimo 8 caracteres.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rol</label>
                        <select name="role" class="form-input">
                            <option value="produccion" {{ old('role') === 'produccion' ? 'selected' : '' }}>Producción</option>
                            <option value="ventas" {{ old('role') === 'ventas' ? 'selected' : '' }}>Ventas</option>
                            <option value="administrativo" {{ old('role', 'administrativo') === 'administrativo' ? 'selected' : '' }}>Administrativo</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions-end">
                    <button type="submit" class="btn btn-primary btn-sm">Crear usuario</button>
                </div>
            </form>
        </div>
    </div>
</x-erp-layout>
