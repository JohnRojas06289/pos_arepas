<?php
use App\Models\Empresa;
use App\Models\Caja;

$empresa = Empresa::first();
if (!$empresa) {
    $empresa = (object)['nombre' => 'Arepas Boyacenses'];
}

// Verificar si hay caja abierta para el usuario actual
$cajaAbierta = null;
try {
    $cajaAbierta = Caja::where('user_id', auth()->id())->where('estado', true)->first();
} catch (\Exception $e) {
    // Si falla silenciosamente, no mostrar el badge
}
?>

<nav class="sb-topnav navbar navbar-expand navbar-dark">

    {{-- Brand / Logo --}}
    <a class="navbar-brand ps-3" href="{{ route('panel') }}">
        <span class="brand-icon">&#127807;</span>
        <span class="d-none d-sm-inline">{{ $empresa->nombre ?? 'Arepas Boyacenses' }}</span>
        <span class="d-inline d-sm-none">AB</span>
    </a>

    {{-- Sidebar Toggle --}}
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-2 me-lg-0 ms-auto" id="sidebarToggle" aria-label="Menú">
        <i class="fas fa-bars"></i>
    </button>

    {{-- Navbar items --}}
    <ul class="navbar-nav ms-auto ms-md-0 me-2 me-lg-3 align-items-center gap-2">

        {{-- Badge de estado de caja --}}
        @if($cajaAbierta)
        <li class="nav-item d-none d-md-flex align-items-center">
            <div class="caja-badge open">
                <span class="dot"></span>
                Caja abierta
            </div>
        </li>
        @else
        <li class="nav-item d-none d-md-flex align-items-center">
            <div class="caja-badge close">
                <span class="dot"></span>
                Sin caja
            </div>
        </li>
        @endif

        {{-- Nombre del usuario --}}
        <li class="nav-item d-none d-lg-flex align-items-center">
            <span class="navbar-user-name">{{ auth()->user()->name }}</span>
        </li>

        {{-- Dropdown de usuario --}}
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button"
               data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown" style="min-width:200px;">
                <li class="px-3 py-2">
                    <div style="font-weight:700;font-size:0.9rem;color:var(--text-primary);">{{ auth()->user()->name }}</div>
                    <div style="font-size:0.75rem;color:var(--text-secondary);">
                        {{ auth()->user()->getRoleNames()->first() ?? 'Usuario' }}
                    </div>
                </li>
                <li><hr class="dropdown-divider" /></li>
                @can('ver-perfil')
                <li>
                    <a class="dropdown-item" href="{{ route('profile.index') }}">
                        <i class="fas fa-cog"></i> Configuraciones
                    </a>
                </li>
                @endcan
                @can('ver-registro-actividad')
                <li>
                    <a class="dropdown-item" href="{{ route('activityLog.index') }}">
                        <i class="fas fa-history" style="color:var(--color-info);"></i> Registro de actividad
                    </a>
                </li>
                @endcan
                <li><hr class="dropdown-divider" /></li>
                <li>
                    <a class="dropdown-item text-danger" href="{{ route('logout') }}">
                        <i class="fas fa-sign-out-alt" style="color:var(--color-danger);"></i> Cerrar sesión
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</nav>
