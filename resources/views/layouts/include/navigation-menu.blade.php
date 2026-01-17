<style>
    /* Modern Sidebar Styles */
    .sb-sidenav-dark {
        background: linear-gradient(180deg, #1f2937 0%, #111827 100%) !important;
    }

    .sb-sidenav-dark .sb-sidenav-menu {
        background: transparent;
    }

    .sb-sidenav-dark .nav-link {
        color: rgba(255, 255, 255, 0.8) !important;
        padding: 0.75rem 1rem !important;
        border-radius: 8px !important;
        margin: 0.25rem 0.5rem !important;
        transition: all 0.3s ease !important;
        font-weight: 500 !important;
    }

    .sb-sidenav-dark .nav-link:hover {
        background-color: rgba(245, 158, 11, 0.1) !important;
        color: #fbbf24 !important;
        transform: translateX(4px);
    }

    .sb-sidenav-dark .nav-link.active {
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%) !important;
        color: white !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.3);
    }

    .sb-sidenav-dark .nav-link .sb-nav-link-icon {
        color: rgba(255, 255, 255, 0.6);
        margin-right: 0.75rem;
        width: 20px;
        text-align: center;
    }

    .sb-sidenav-dark .nav-link:hover .sb-nav-link-icon,
    .sb-sidenav-dark .nav-link.active .sb-nav-link-icon {
        color: white;
    }

    .sb-sidenav-dark .sb-sidenav-menu-heading {
        color: rgba(255, 255, 255, 0.4) !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.1em !important;
        padding: 1.5rem 1rem 0.5rem !important;
        margin-top: 0.5rem !important;
    }

    .sb-sidenav-dark .sb-sidenav-footer {
        background: rgba(0, 0, 0, 0.2) !important;
        border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
        padding: 1rem !important;
    }

    .sb-sidenav-dark .sb-sidenav-footer .small {
        color: rgba(255, 255, 255, 0.5) !important;
        font-size: 0.75rem !important;
    }

    /* Collapsed Menu Styles */
    .sb-sidenav-dark .sb-sidenav-collapse-arrow {
        color: rgba(255, 255, 255, 0.5);
        transition: transform 0.3s ease;
    }

    .sb-sidenav-dark .nav-link[aria-expanded="true"] .sb-sidenav-collapse-arrow {
        transform: rotate(90deg);
        color: #fbbf24;
    }

    .sb-sidenav-dark .sb-sidenav-menu-nested {
        padding-left: 0 !important;
    }

    .sb-sidenav-dark .sb-sidenav-menu-nested .nav-link {
        padding-left: 3rem !important;
        font-size: 0.9rem !important;
    }
</style>

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">

                @can('ver-panel')
                <x-nav.heading>Inicio</x-nav.heading>
                <x-nav.nav-link content='Panel'
                    icon='fas fa-chart-line'
                    :href="route('panel')" />
                @endcan

                <x-nav.heading>Módulos</x-nav.heading>

                @can('ver-categoria')
                <x-nav.nav-link content='Categorías'
                    icon='fa-solid fa-tag'
                    :href="route('categorias.index')" />
                @endcan

                @can('ver-presentacione')
                <x-nav.nav-link content='Presentaciones'
                    icon='fa-solid fa-box-archive'
                    :href="route('presentaciones.index')" />
                @endcan

                @can('ver-marca')
                <x-nav.nav-link content='Marcas'
                    icon='fa-solid fa-bullhorn'
                    :href="route('marcas.index')" />
                @endcan

                @can('ver-producto')
                <x-nav.nav-link content='Productos'
                    icon='fa-brands fa-shopify'
                    :href="route('productos.index')" />
                @endcan

                @can('ver-inventario')
                <x-nav.nav-link content='Inventario'
                    icon='fa-solid fa-book'
                    :href="route('inventario.index')" />
                @endcan

                @can('ver-kardex')
                <x-nav.nav-link content='Kardex'
                    icon='fa-solid fa-file'
                    :href="route('kardex.index')" />
                @endcan

                @can('ver-cliente')
                <x-nav.nav-link content='Clientes'
                    icon='fa-solid fa-users'
                    :href="route('clientes.index')" />
                @endcan

                @can('ver-proveedore')
                <x-nav.nav-link content='Proveedores'
                    icon='fa-solid fa-user-group'
                    :href="route('proveedores.index')" />
                @endcan

                @can('ver-caja')
                <x-nav.nav-link content='Cajas'
                    icon='fa-solid fa-money-bill'
                    :href="route('cajas.index')" />
                @endcan

                <!----Compras---->
                @can('ver-compra')
                <x-nav.link-collapsed
                    id="collapseCompras"
                    icon="fa-solid fa-store"
                    content="Compras">
                    @can('ver-compra')
                    <x-nav.link-collapsed-item :href="route('compras.index')" content="Ver" />
                    @endcan

                    @can('crear-compra')
                    <x-nav.link-collapsed-item :href="route('compras.create')" content="Crear" />
                    @endcan
                </x-nav.link-collapsed>
                @endcan



                <!----Ventas---->
                @can('ver-venta')
                <x-nav.link-collapsed
                    id="collapseVentas"
                    icon="fa-solid fa-cart-shopping"
                    content="Ventas">
                    @can('ver-venta')
                    <x-nav.link-collapsed-item :href="route('ventas.index')" content="Ver" />
                    @endcan

                    @can('crear-venta')
                    <x-nav.link-collapsed-item :href="route('ventas.create')" content="Crear" />
                    @endcan
                </x-nav.link-collapsed>
                @endcan

                @hasrole('administrador')
                <x-nav.heading>Administración</x-nav.heading>
                @endhasrole

                @can('ver-empresa')
                <x-nav.nav-link content='Empresa'
                    icon='fa-solid fa-city'
                    :href="route('empresa.index')" />
                @endcan

                @can('ver-empleado')
                <x-nav.nav-link content='Empleados'
                    icon='fa-solid fa-users'
                    :href="route('empleados.index')" />
                @endcan

                @can('ver-user')
                <x-nav.nav-link content='Usuarios'
                    icon='fa-solid fa-user'
                    :href="route('users.index')" />
                @endcan

                @can('ver-role')
                <x-nav.nav-link content='Roles'
                    icon='fa-solid fa-person-circle-plus'
                    :href="route('roles.index')" />
                @endcan


            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Bienvenido:</div>
            <strong>{{ auth()->user()->name }}</strong>
        </div>
    </nav>
</div>

