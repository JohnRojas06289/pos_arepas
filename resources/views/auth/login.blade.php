<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Arepas Boyacenses - Iniciar sesión</title>

    <script>
        (function () {
            try {
                var t = localStorage.getItem('pos-arepas-theme') || 'light';
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) {}
        }());
    </script>

    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/pos-theme.css') }}" rel="stylesheet" />
    <script src="{{ asset('js/fontawesome.js') }}" crossorigin="anonymous"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--login-bg, #FAF0E4);
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(200, 85, 61, 0.18) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 80%, rgba(240, 199, 94, 0.22) 0%, transparent 55%),
                radial-gradient(ellipse at 50% 50%, rgba(45, 58, 58, 0.07) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(200, 85, 61, 0.08) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            padding: 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-card {
            background: var(--bg-card, #FFFFFF);
            border: 1px solid var(--border-color, #E5E1DB);
            border-radius: 20px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.14), 0 4px 16px rgba(200, 85, 61, 0.10);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
            transition: background-color 0.25s ease, border-color 0.25s ease;
        }

        .login-card-header {
            background: linear-gradient(135deg, var(--color-secondary, #2D3A3A) 0%, #3d4f4f 100%);
            padding: 2.25rem 2rem 1.75rem;
            text-align: center;
        }

        .login-logo {
            font-size: 3rem;
            line-height: 1;
            margin-bottom: 0.5rem;
            display: block;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.25));
        }

        .login-brand {
            font-size: 1.45rem;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: -0.02em;
            margin: 0;
            line-height: 1.2;
        }

        .login-subtitle {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 4px;
            font-weight: 500;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .login-card-body {
            padding: 2rem;
        }

        .login-helper {
            color: var(--text-secondary, #6B7280);
            font-size: 0.85rem;
            line-height: 1.5;
            margin-bottom: 1.25rem;
        }

        .login-alert {
            background: rgba(231, 76, 94, 0.10);
            border-left: 4px solid #E74C5E;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.84rem;
            color: #c73c4e;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .login-label {
            font-weight: 600;
            font-size: 0.82rem;
            color: var(--text-primary, #1A1A2E);
            margin-bottom: 5px;
            display: block;
            letter-spacing: 0.01em;
        }

        .login-input {
            width: 100%;
            background: var(--bg-input, #FFFFFF);
            border: 1.5px solid var(--border-input, #D8D4CE);
            border-radius: 10px;
            padding: 0.65rem 0.9rem;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            color: var(--text-primary, #1A1A2E);
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
            outline: none;
            min-height: 44px;
        }

        .login-input:focus {
            border-color: #C8553D;
            box-shadow: 0 0 0 3px rgba(200, 85, 61, 0.12);
        }

        .password-group {
            position: relative;
        }

        .password-group .login-input {
            padding-right: 3rem;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-secondary, #6B7280);
            padding: 4px;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .toggle-password:hover {
            color: #C8553D;
        }

        .login-btn {
            width: 100%;
            height: 48px;
            background: #C8553D;
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.18s ease, box-shadow 0.18s ease, transform 0.15s ease;
            box-shadow: 0 4px 14px rgba(200, 85, 61, 0.30);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 1.5rem;
        }

        .login-btn:hover {
            background: #A8432D;
            box-shadow: 0 6px 20px rgba(200, 85, 61, 0.40);
            transform: translateY(-1px);
        }

        .login-card-footer {
            border-top: 1px solid var(--border-color, #E5E1DB);
            padding: 1rem 2rem;
            text-align: center;
            background: var(--bg-card, #FFFFFF);
        }

        .login-card-footer a {
            font-size: 0.8rem;
            color: var(--text-secondary, #6B7280);
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .login-card-footer a:hover {
            color: #C8553D;
        }

        [data-theme="dark"] body,
        [data-theme="dark"] {
            --login-bg: #0D0D12;
        }

        [data-theme="dark"] body {
            background-color: #0D0D12;
        }

        [data-theme="dark"] body::after {
            background-image: radial-gradient(circle, rgba(240, 199, 94, 0.06) 1px, transparent 1px);
        }

        [data-theme="dark"] body::before {
            background:
                radial-gradient(ellipse at 20% 20%, rgba(200, 85, 61, 0.15) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 80%, rgba(240, 199, 94, 0.12) 0%, transparent 55%),
                radial-gradient(ellipse at 50% 50%, rgba(0, 0, 0, 0.3) 0%, transparent 70%);
        }

        @media (max-width: 480px) {
            .login-card { border-radius: 14px; }
            .login-card-body { padding: 1.5rem; }
            .login-card-header { padding: 1.75rem 1.5rem 1.5rem; }
            .login-brand { font-size: 1.25rem; }
            .login-logo { font-size: 2.5rem; }
        }

        .form-field { margin-bottom: 1.1rem; }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-card-header">
                <span class="login-logo">&#127805;</span>
                <h1 class="login-brand">Arepas Boyacenses</h1>
                <p class="login-subtitle">Punto de venta</p>
            </div>

            <div class="login-card-body">
                <p class="login-helper">
                    Inicia sesión con tu usuario asignado para entrar al sistema y continuar con la operación del día.
                </p>

                @if ($errors->any())
                    @foreach ($errors->all() as $item)
                    <div class="login-alert" role="alert">
                        <i class="fas fa-exclamation-circle" style="margin-top:1px;flex-shrink:0;"></i>
                        <span>{{ $item }}</span>
                    </div>
                    @endforeach
                @endif

                <form action="{{ route('login.login') }}" method="post">
                    @csrf

                    <div class="form-field">
                        <label class="login-label" for="inputEmail">
                            <i class="fas fa-envelope me-1" style="color:#C8553D;"></i>
                            Correo electrónico
                        </label>
                        <input
                            autofocus
                            autocomplete="email"
                            autocapitalize="off"
                            class="login-input"
                            name="email"
                            id="inputEmail"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="correo@ejemplo.com"
                            required
                        />
                    </div>

                    <div class="form-field">
                        <label class="login-label" for="inputPassword">
                            <i class="fas fa-lock me-1" style="color:#C8553D;"></i>
                            Contraseña
                        </label>
                        <div class="password-group">
                            <input
                                class="login-input"
                                name="password"
                                id="inputPassword"
                                type="password"
                                autocomplete="current-password"
                                placeholder="Ingresa tu contraseña"
                                required
                            />
                            <button class="toggle-password" type="button" id="togglePassword" aria-label="Mostrar u ocultar contraseña">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Ingresar al sistema
                    </button>
                </form>
            </div>

            <div class="login-card-footer">
                <a href="{{ url('/') }}">
                    <i class="fas fa-arrow-left me-1"></i>Volver al inicio
                </a>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            var input = document.getElementById('inputPassword');
            var icon = document.getElementById('toggleIcon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>
</body>

</html>
