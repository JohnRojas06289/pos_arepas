<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Inicio de sesión del sistema" />
    <meta name="author" content="POS Arepas" />
    <title>POS Arepas - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }
        .card {
            background-color: rgba(33, 37, 41, 0.9);
            border: 1px solid #4b5563;
        }
        .btn-warning {
            background-color: #f59e0b;
            border-color: #f59e0b;
            color: #000;
            font-weight: bold;
        }
        .btn-warning:hover {
            background-color: #d97706;
            border-color: #d97706;
        }
        .form-control:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 0.25rem rgba(245, 158, 11, 0.25);
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100 text-light">
    <div id="layoutAuthentication" class="flex-grow-1 d-flex flex-column justify-content-center">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg rounded-lg mt-5">
                                <div class="card-header border-bottom border-secondary">
                                    <h3 class="text-center font-weight-light my-4 text-warning">🌽 Acceso al Sistema</h3>
                                </div>
                                <div class="card-body">
                                    @if ($errors->any())
                                    @foreach ($errors->all() as $item)
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{$item}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    @endforeach
                                    @endif
                                    <form action="{{route('login.login')}}" method="post">
                                        @csrf
                                        <div class="form-floating mb-3 text-dark">
                                            <input autofocus autocomplete="off" value="invitado@gmail.com" class="form-control" name="email" id="inputEmail" type="email" placeholder="name@example.com" />
                                            <label for="inputEmail">Correo electrónico</label>
                                        </div>
                                        <div class="input-group mb-3">
                                            <div class="form-floating text-dark flex-grow-1">
                                                <input class="form-control" name="password" value="12345678" id="inputPassword" type="password" placeholder="Password" />
                                                <label for="inputPassword">Contraseña</label>
                                            </div>
                                            <button class="btn btn-outline-secondary bg-white border-start-0" type="button" id="togglePassword" style="border-color: #ced4da;">
                                                <i class="fa-solid fa-eye text-muted"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="d-grid gap-2 mt-4 mb-0">
                                            <button class="btn btn-warning btn-lg" type="submit">Iniciar Sesión</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center py-3 border-top border-secondary">
                                    <div class="small"><a href="{{ route('panel') }}" class="text-secondary text-decoration-none">Volver al inicio</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('inputPassword');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>

</html>

