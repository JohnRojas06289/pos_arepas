<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Arepas Boyacenses - El sabor de siempre" />
    <meta name="author" content="Arepas Boyacenses" />
    <title>@yield('title', 'Arepas Boyacenses')</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Custom CSS -->
    <link href="{{ asset('css/bajocero.css') }}" rel="stylesheet" />
    @stack('css')
    <script>
        // Init Theme
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container-fluid px-5">
            <a class="navbar-brand" href="{{ route('home') }}">
                🫓 Arepas<span style="color: var(--text-color); font-weight: 400"> Boyacenses</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
                <i class="fas fa-bars" style="color: var(--primary-color);"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ms-auto py-4 py-lg-0 align-items-center">
                    <li class="nav-item"><a class="nav-link px-lg-3 {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link px-lg-3 {{ request()->routeIs('collection') ? 'active' : '' }}" href="{{ route('collection') }}">Colección</a></li>
                    <li class="nav-item"><a class="nav-link px-lg-3 {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contacto</a></li>
                    <li class="nav-item"><a class="nav-link px-lg-3 {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">Nosotros</a></li>
                    
                    <!-- Theme Toggle -->
                    <li class="nav-item ms-lg-3 me-2">
                        <button class="theme-toggle-btn" id="themeToggle" title="Cambiar Tema">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>

                    <li class="nav-item ms-lg-2">
                        <a href="{{ route('login.index') }}" class="btn-login">
                            <i class="fas fa-user me-2"></i> Login
                        </a>
                    </li>
                    <li class="nav-item ms-3">
                        <a href="#" class="position-relative text-white">
                            <i class="fas fa-shopping-bag fa-lg"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;" id="cartCountNav">
                                0
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    <footer>
        <div class="container px-5">
            <div class="row gx-5">
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <div class="footer-heading">🫓 Arepas Boyacenses</div>
                    <p class="text-muted mb-4">El sabor auténtico de Boyacá en cada mordida. Arepas artesanales hechas con amor y tradición.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-tiktok fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-5 mb-lg-0">
                    <div class="footer-heading">Explorar</div>
                    <div class="footer-links">
                        <a href="{{ route('home') }}">Inicio</a>
                        <a href="{{ route('collection') }}">Catálogo</a>
                        <a href="{{ route('about') }}">Nosotros</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 mb-5 mb-lg-0">
                    <div class="footer-heading">Contacto</div>
                    <div class="footer-links">
                        <div class="mb-2"><i class="fas fa-map-marker-alt me-2 text-primary"></i> Bogotá, Colombia</div>
                        <div class="mb-2"><i class="fab fa-whatsapp me-2 text-success"></i> +57 300 123 4567</div>
                        <div class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i> contacto@bajocero.com</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4">
                    <div class="footer-heading">Pagos Seguros</div>
                    <p class="text-muted small">Procesamos tus pagos con la máxima seguridad.</p>
                    <div class="d-flex gap-3 text-white fs-4">
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5 pt-4 border-top border-secondary">
                <small class="text-muted">&copy; 2026 Bajo Cero. Todos los derechos reservados.</small>
            </div>
        </div>
    </footer>

    <!-- Whatsapp Floating Button -->
    <a href="https://wa.me/573212335821" target="_blank" style="position: fixed; bottom: 20px; right: 20px; background-color: #25d366; color: white; width: 60px; height: 60px; border-radius: 50%; text-align: center; line-height: 60px; font-size: 30px; z-index: 1000; box-shadow: 0 4px 10px rgba(0,0,0,0.5);">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('js')
    
    <script>
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = themeToggleBtn.querySelector('i');
        const rootElement = document.documentElement;

        function updateIcon(theme) {
            if (theme === 'light') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
        }

        // Init icon state
        updateIcon(localStorage.getItem('theme') || 'dark');

        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = rootElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            rootElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });
    </script>
</body>
</html>
