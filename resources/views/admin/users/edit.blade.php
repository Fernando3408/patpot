<form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf
    @method('PUT')

    <div class="form-section-header">Editar usuario</div>

    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Nueva contraseña (dejar vacío para no cambiar)</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Roles</label>
            @foreach(\App\Models\Role::all() as $role)
                <label class="form-check">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                        {{ old('roles', $user->roles->pluck('id')->toArray()) ? (in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '') : ($user->roles->contains($role->id) ? 'checked' : '') }} class="form-check-input">
                    {{ $role->name }}
                </label>
            @endforeach
            @error('roles')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar</button>
    </div>
</form>
