<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Portal de empleados para POS Arepas" />
    <meta name="author" content="POS Arepas" />
    <title>POS Arepas - Acceso Empleados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <style>
        .hero-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            text-align: center;
            border-bottom: 4px solid #f59e0b; /* Arepa color accent */
        }
        .accordion-button:not(.collapsed) {
            background-color: #f59e0b;
            color: #000;
        }
        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(245, 158, 11, 0.25);
            border-color: #f59e0b;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-dark text-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-md navbar-dark bg-secondary bg-opacity-25">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{route('panel')}}">
                🌽 POS Arepas
            </a>
            <div class="ms-auto">
                <form class="d-flex" action="{{route('login.index')}}" method="get">
                    <button class="btn btn-outline-warning" type="submit">Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Sistema de Gestión</h1>
            <p class="lead mb-4 text-light text-opacity-75">Portal exclusivo para empleados y administración.</p>
            <a href="{{route('login.index')}}" class="btn btn-warning btn-lg px-5 fw-bold text-dark">Ingresar al Sistema</a>
        </div>
    </section>

    <!-- Features Accordion -->
    <div class="container my-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h4 class="text-center mb-4 text-secondary">Herramientas Disponibles</h4>
                <div class="accordion" id="featuresAccordion">
                    <div class="accordion-item bg-secondary bg-opacity-10 border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                🍔 Gestión de Ventas
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#featuresAccordion">
                            <div class="accordion-body text-light text-opacity-75">
                                Registra pedidos de forma rápida. Personaliza las arepas con ingredientes extra y gestiona los acompañamientos al instante.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item bg-secondary bg-opacity-10 border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                📊 Informes y Reportes
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#featuresAccordion">
                            <div class="accordion-body text-light text-opacity-75">
                                Visualiza el rendimiento diario y mensual. Controla las ganancias, cierres de caja y productos más vendidos.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item bg-secondary bg-opacity-10 border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                📦 Inventario
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#featuresAccordion">
                            <div class="accordion-body text-light text-opacity-75">
                                Control total del stock de ingredientes (masas, quesos, carnes). Alertas automáticas de stock bajo.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item bg-secondary bg-opacity-10 border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                👥 Gestión de Personal
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#featuresAccordion">
                            <div class="accordion-body text-light text-opacity-75">
                                Administración de turnos, permisos y roles de usuarios dentro del sistema POS.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center text-secondary py-4 bg-black bg-opacity-25 mt-auto">
        <div class="container">
            <small>© {{ date('Y') }} POS Arepas. Sistema de uso interno.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
</body>
</html>