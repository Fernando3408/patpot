<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña - PatPot</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="auth-page">

    <div class="auth-container">
        <div class="card auth-card">
            
            <div class="auth-header">
                <p class="auth-brand">PatPot</p>
                <h1 class="auth-title">Restablecer contraseña</h1>
                <p class="auth-subtitle">Ingresa tu nueva contraseña.</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                @if($errors->any())
                    <div class="alert-error">
                        @foreach($errors->all() as $error)
                            <p class="alert-error-text">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="form-group mb-4">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        class="form-input" 
                        value="{{ $email ?? old('email') }}" 
                        autocomplete="email" 
                        required 
                        autofocus
                    >
                </div>

                <div class="form-group mb-4">
                    <label class="form-label" for="password">Nueva contraseña</label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        class="form-input" 
                        autocomplete="new-password" 
                        required
                    >
                </div>

                <div class="form-group form-group--mb-lg">
                    <label class="form-label" for="password_confirmation">Confirmar contrasena</label>
                    <input 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        type="password" 
                        class="form-input" 
                        autocomplete="new-password" 
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    Restablecer contraseña
                </button>

                <div class="forgot-password-row">
                    <a href="{{ route('login') }}" class="link-muted">Volver al inicio de sesión</a>
                </div>
            </form>

        </div>
    </div>

</body>
</html>
