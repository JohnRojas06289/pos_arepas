<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Arepas Boyacenses - El sabor de siempre" />
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
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>

    <!-- Navbar mínima: solo marca + carrito -->
    <nav class="navbar navbar-custom fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="{{ route('collection') }}">
                🫓 Arepas<span style="color: var(--text-color); font-weight: 400"> Boyacenses</span>
            </a>

            <div class="d-flex align-items-center gap-3">
                <!-- Theme Toggle -->
                <button class="theme-toggle-btn" id="themeToggle" title="Cambiar tema">
                    <i class="fas fa-moon"></i>
                </button>

                <!-- Carrito (abre offcanvas) -->
                <button data-bs-toggle="offcanvas"
                        data-bs-target="#cartOffcanvas"
                        class="position-relative"
                        style="background:none;border:none;color:var(--text-color);font-size:1.3rem;cursor:pointer;padding:4px 8px;">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                          style="font-size:0.6rem;" id="cartCountNav">0</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Contenido -->
    @yield('content')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('js')

    <script>
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = themeToggleBtn.querySelector('i');

        function updateIcon(theme) {
            themeIcon.classList.toggle('fa-moon', theme !== 'light');
            themeIcon.classList.toggle('fa-sun',  theme === 'light');
        }

        updateIcon(localStorage.getItem('theme') || 'dark');

        themeToggleBtn.addEventListener('click', () => {
            const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            updateIcon(next);
        });
    </script>
</body>
</html>
