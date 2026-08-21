<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - PatPot</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body style="background-color: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 1.5rem;">

    <div style="width: 100%; max-width: 420px;">
        <div class="card" style="padding: 2rem;">
            
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <p style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; color: #4f46e5; margin-bottom: 0.5rem;">PatPot</p>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Iniciar sesión</h1>
                <p style="font-size: 0.875rem; color: #64748b; margin-top: 0.25rem;">Accede al ERP operativo.</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

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
                        autofocus
                    >
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="password">Contraseña</label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        class="form-input" 
                        autocomplete="current-password" 
                        required
                    >
                </div>

                <div style="margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="remember" name="remember" value="1" style="cursor: pointer;">
                    <label for="remember" style="font-size: 0.875rem; color: #334155; cursor: pointer;">Mantener mi sesión iniciada</label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.75rem;">
                    Entrar al ERP
                </button>
            </form>

        </div>
    </div>

</body>
</html>