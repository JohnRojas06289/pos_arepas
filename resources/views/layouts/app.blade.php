<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Sistema de ventas de abarrotes" />
    <meta name="author" content="SakCode" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema ventas - @yield('title')</title>
    @stack('css-datatable')
    <!--link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous"--->
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
    <script src="{{ asset('js/fontawesome.js') }}" crossorigin="anonymous"></script>
    @stack('css')
</head>

<body class="sb-nav-fixed">

    @include('layouts.include.navigation-header')

    <div id="layoutSidenav">

        @include('layouts.include.navigation-menu')

        <div id="layoutSidenav_content">

            @include('layouts.partials.alert')

            <main>
                @yield('content')
            </main>



        </div>
    </div>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const notificationIcon = document.getElementById('notificationsDropdown');

            notificationIcon.addEventListener('click', function() {
                fetch("{{ route('notifications.markAsRead') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const badge = notificationIcon.querySelector('.badge');
                            if (badge) badge.remove();
                        }
                    })
                    .catch(error => console.error('Error al marcar notificaciones como leídas:', error));
            });

        });


    </script>
    @stack('js')

</body>


</html>
