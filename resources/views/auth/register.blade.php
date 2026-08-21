<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - PatPot</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body style="background-color: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 1.5rem;">

    <div style="width: 100%; max-width: 420px;">
        <div class="card" style="padding: 2rem;">
            
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <p style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: #4f46e5; margin-bottom: 0.5rem;">PatPot</p>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Crear cuenta</h1>
                <p style="font-size: 0.875rem; color: #64748b; margin-top: 0.25rem;">Regístrate para acceder al ERP operativo.</p>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="name">Nombre</label>
                    <input 
                        id="name" 
                        name="name" 
                        type="text" 
                        class="form-input" 
                        value="{{ old('name') }}" 
                        autocomplete="name" 
                        required 
                        autofocus
                    >
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        class="form-input" 
                        value="{{ old('email') }}" 
                        autocomplete="email" 
                        required
                    >
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="password">Contraseña</label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        class="form-input" 
                        autocomplete="new-password" 
                        required
                    >
                    <small style="display: block; margin-top: 0.25rem; font-size: 0.75rem; color: #64748b;">Mínimo 8 caracteres.</small>
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
                    <input 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        type="password" 
                        class="form-input" 
                        autocomplete="new-password" 
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.75rem;">
                    Crear cuenta
                </button>
            </form>

            <p style="text-align: center; font-size: 0.875rem; color: #64748b; margin-top: 1.5rem; margin-bottom: 0;">
                ¿Ya tienes cuenta? 
                <a href="{{ route('login') }}" style="color: #4f46e5; font-weight: 500; text-decoration: none;">Inicia sesión</a>
            </p>

        </div>
    </div>

</body>
</html>