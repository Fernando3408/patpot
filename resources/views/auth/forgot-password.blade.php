<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - PatPot</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="auth-page">

    <div class="auth-container">
        <div class="card auth-card">
            
            <div class="auth-header">
                <p class="auth-brand">PatPot</p>
                <h1 class="auth-title">Recuperar contraseña</h1>
                <p class="auth-subtitle">Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>
            </div>

            @if(session('status'))
                <div class="alert-success">
                    <p class="alert-success-text">{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

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
                        value="{{ old('email') }}" 
                        autocomplete="email" 
                        required 
                        autofocus
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    Enviar enlace de recuperación
                </button>

                <div class="forgot-password-row">
                    <a href="{{ route('login') }}" class="link-muted">Volver al inicio de sesión</a>
                </div>
            </form>

        </div>
    </div>

</body>
</html>
