<x-erp-layout title="Editar Usuario">
    <div class="card">
        <div class="card__header">
            <h1 class="card__title">{{ $user->name }}</h1>
        </div>
        <div class="card__body">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-input" value="{{ $user->name }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" value="{{ $user->email }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nueva contraseña (dejar vacío para no cambiar)</label>
                        <input type="password" name="password" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Roles</label>
                        @foreach(\App\Models\Role::all() as $role)
                            <label>
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                    {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                {{ $role->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div style="margin-top:1.5rem;text-align:right;">
                    <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</x-erp-layout>
