@php
    $comprasExpanded = request()->routeIs('compras.*');
@endphp

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">

        <div class="sb-sidenav-brand-header">
            <span class="brand-icon" style="font-size:1.3rem;">&#127807;</span>
            <div>
                <div class="sb-sidenav-brand-name">{{ $layoutCompanyName ?? 'Arepas Boyacenses' }}</div>
                <div class="sb-sidenav-brand-version">Sistema POS v2.0</div>
            </div>
        </div>

        <div class="sb-sidenav-menu">
            <div class="nav flex-column py-2">

                @canany(['ver-panel', 'crear-venta', 'ver-venta', 'ver-caja', 'ver-movimiento'])
                <x-nav.heading>Ventas</x-nav.heading>
                @endcanany

                @can('ver-panel')
                <x-nav.nav-link
                    content="Inicio"
                    icon="fas fa-chart-line"
                    :href="route('panel')"
                    :active="request()->routeIs('panel')" />
                @endcan

                @can('crear-venta')
                <x-nav.nav-link
                    content="Punto de Venta"
                    icon="fas fa-cash-register"
                    :href="route('ventas.create')"
                    :active="request()->routeIs('ventas.create')" />
                @endcan

                @can('ver-venta')
                <x-nav.nav-link
                    content="Historial de Ventas"
                    icon="fas fa-receipt"
                    :href="route('ventas.index')"
                    :active="request()->routeIs('ventas.index', 'ventas.show')" />
                @endcan

                @can('ver-caja')
                <x-nav.nav-link
                    content="Cajas"
                    icon="fas fa-money-bill-wave"
                    :href="route('cajas.index')"
                    :active="request()->routeIs('cajas.*')" />
                @endcan

                @can('ver-movimiento')
                <x-nav.nav-link
                    content="Movimientos"
                    icon="fas fa-exchange-alt"
                    :href="route('movimientos.index')"
                    :active="request()->routeIs('movimientos.*')" />
                @endcan

                @canany(['ver-producto', 'ver-inventario', 'ver-kardex', 'ver-compra'])
                <x-nav.heading>Inventario</x-nav.heading>
                @endcanany

                @can('ver-producto')
                <x-nav.nav-link
                    content="Productos"
                    icon="fa-solid fa-box-open"
                    :href="route('productos.index')"
                    :active="request()->routeIs('productos.*')" />
                @endcan

                @can('ver-inventario')
                <x-nav.nav-link
                    content="Inventario"
                    icon="fa-solid fa-warehouse"
                    :href="route('inventario.index')"
                    :active="request()->routeIs('inventario.*')" />
                @endcan

                @can('ver-kardex')
                <x-nav.nav-link
                    content="Kardex"
                    icon="fa-solid fa-file-lines"
                    :href="route('kardex.index')"
                    :active="request()->routeIs('kardex.*')" />
                @endcan

                @can('ver-compra')
                <x-nav.link-collapsed
                    id="collapseCompras"
                    icon="fa-solid fa-store"
                    content="Compras"
                    :active="$comprasExpanded"
                    :expanded="$comprasExpanded">
                    @can('ver-compra')
                    <x-nav.link-collapsed-item
                        :href="route('compras.index')"
                        content="Ver compras"
                        :active="request()->routeIs('compras.index', 'compras.show')" />
                    @endcan
                    @can('crear-compra')
                    <x-nav.link-collapsed-item
                        :href="route('compras.create')"
                        content="Nueva compra"
                        :active="request()->routeIs('compras.create')" />
                    @endcan
                </x-nav.link-collapsed>
                @endcan

                @canany(['ver-categoria', 'ver-marca', 'ver-presentacione'])
                <x-nav.heading>Catálogo</x-nav.heading>
                @endcanany

                @can('ver-categoria')
                <x-nav.nav-link
                    content="Categorías"
                    icon="fa-solid fa-tag"
                    :href="route('categorias.index')"
                    :active="request()->routeIs('categorias.*')" />
                @endcan

                @can('ver-marca')
                <x-nav.nav-link
                    content="Marcas"
                    icon="fa-solid fa-copyright"
                    :href="route('marcas.index')"
                    :active="request()->routeIs('marcas.*')" />
                @endcan

                @can('ver-presentacione')
                <x-nav.nav-link
                    content="Presentaciones"
                    icon="fa-solid fa-ruler-combined"
                    :href="route('presentaciones.index')"
                    :active="request()->routeIs('presentaciones.*')" />
                @endcan

                @canany(['ver-cliente', 'ver-proveedore', 'ver-empleado'])
                <x-nav.heading>Personas</x-nav.heading>
                @endcanany

                @can('ver-cliente')
                <x-nav.nav-link
                    content="Clientes"
                    icon="fa-solid fa-users"
                    :href="route('clientes.index')"
                    :active="request()->routeIs('clientes.*')" />
                @endcan

                @can('ver-proveedore')
                <x-nav.nav-link
                    content="Proveedores"
                    icon="fa-solid fa-user-tie"
                    :href="route('proveedores.index')"
                    :active="request()->routeIs('proveedores.*')" />
                @endcan

                @can('ver-empleado')
                <x-nav.nav-link
                    content="Empleados"
                    icon="fa-solid fa-id-card"
                    :href="route('empleados.index')"
                    :active="request()->routeIs('empleados.*')" />
                @endcan

                @hasrole('administrador')
                <x-nav.heading>Administración</x-nav.heading>

                <x-nav.nav-link
                    content="Estadísticas"
                    icon="fas fa-chart-pie"
                    :href="route('admin.estadisticas')"
                    :active="request()->routeIs('admin.estadisticas')" />

                @can('ver-empresa')
                <x-nav.nav-link
                    content="Empresa"
                    icon="fa-solid fa-building"
                    :href="route('empresa.index')"
                    :active="request()->routeIs('empresa.*')" />
                @endcan

                @can('ver-user')
                <x-nav.nav-link
                    content="Usuarios"
                    icon="fa-solid fa-user-gear"
                    :href="route('users.index')"
                    :active="request()->routeIs('users.*')" />
                @endcan

                @can('ver-role')
                <x-nav.nav-link
                    content="Roles y Permisos"
                    icon="fa-solid fa-shield-halved"
                    :href="route('roles.index')"
                    :active="request()->routeIs('roles.*')" />
                @endcan
                @endhasrole

                @can('ver-registro-actividad')
                <x-nav.heading>Sistema</x-nav.heading>
                <x-nav.nav-link
                    content="Registro de Actividad"
                    icon="fas fa-history"
                    :href="route('activityLog.index')"
                    :active="request()->routeIs('activityLog.*')" />
                @endcan

            </div>
        </div>

        <div class="theme-toggle-wrapper">
            <label class="theme-toggle-label" for="theme-toggle-checkbox">
                <i class="fas fa-sun" id="theme-icon"></i>
                <span id="theme-label-text">Modo oscuro</span>
            </label>
            <label class="theme-switch">
                <input type="checkbox" id="theme-toggle-checkbox" autocomplete="off">
                <span class="theme-switch-slider"></span>
            </label>
        </div>

        <div class="sb-sidenav-footer">
            <div class="user-label">Conectado como</div>
            <span class="user-name">{{ auth()->user()->name }}</span>
            <div>
                <span class="user-role-badge">
                    {{ auth()->user()->getRoleNames()->first() ?? 'usuario' }}
                </span>
            </div>
        </div>

    </nav>
</div>
