<?php

use App\Models\Empresa;

$empresa = Empresa::first();
if (!$empresa) {
    $empresa = (object)['nombre' => 'POS Arepas'];
}
?>
<style>
    /* Modern Navigation Header Styles */
    .sb-topnav {
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%) !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border-bottom: 2px solid #2563eb;
    }

    .navbar-brand {
        font-weight: 700 !important;
        font-size: 1.25rem !important;
        color: white !important;
        transition: all 0.3s ease;
    }

    .navbar-brand:hover {
        transform: scale(1.05);
    }

    /* Enhanced Notification Badge */
    .notification-badge {
        background: #dc2626 !important;
        font-size: 0.75rem !important;
        padding: 0.25rem 0.5rem !important;
        border-radius: 9999px !important;
        font-weight: 700 !important;
        position: absolute;
        top: -5px;
        right: -5px;
        min-width: 20px;
        text-align: center;
    }

    .nav-link.dropdown-toggle {
        position: relative;
        transition: all 0.3s ease;
        border-radius: 8px;
        padding: 0.5rem 0.75rem !important;
    }

    .nav-link.dropdown-toggle:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    /* Enhanced Dropdown Menu */
    .dropdown-menu {
        border-radius: 12px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
        padding: 0.5rem !important;
        margin-top: 0.5rem !important;
    }

    .dropdown-item {
        border-radius: 8px !important;
        padding: 0.625rem 1rem !important;
        transition: all 0.2s ease;
        margin-bottom: 0.25rem;
    }

    .dropdown-item:hover {
        background-color: #dbeafe !important;
        color: #1e40af !important;
        transform: translateX(4px);
    }

    .dropdown-item:last-child {
        margin-bottom: 0;
    }

    .notification-item {
        border-left: 3px solid #2563eb;
        background-color: #eff6ff;
    }

    .notification-item:hover {
        background-color: #dbeafe !important;
    }

    .notification-time {
        font-size: 0.75rem;
        color: #6b7280;
    }

    /* Sidebar Toggle Button */
    #sidebarToggle {
        transition: all 0.3s ease;
        border-radius: 8px;
        padding: 0.5rem 0.75rem !important;
    }

    #sidebarToggle:hover {
        background-color: rgba(255, 255, 255, 0.1) !important;
        transform: rotate(90deg);
    }
</style>

<nav class="sb-topnav navbar navbar-expand navbar-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="{{ route('panel') }}">
        <i class="fas fa-utensils me-2"></i>{{ $empresa->nombre ?? 'POS Arepas' }}
    </a>
    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Navbar-->
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <!-- Notifications Dropdown -->
        <li class="nav-item dropdown me-3">
            <a class="nav-link dropdown-toggle" href="#" role="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell fa-lg"></i>
                @if(Auth::user()->unreadNotifications->count() > 0)
                    <span class="notification-badge">{{ Auth::user()->unreadNotifications->count() }}</span>
                @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" style="min-width: 320px;">
                <li class="px-3 py-2 border-bottom">
                    <strong class="text-dark">Notificaciones</strong>
                </li>
                @forelse (Auth::user()->unreadNotifications->take(5) as $notification)
                <li>
                    <a href="#" class="dropdown-item notification-item">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle text-primary me-2 mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $notification->data['message'] ?? 'Nueva notificación' }}</div>
                                <small class="notification-time">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </a>
                </li>
                @empty
                <li>
                    <div class="dropdown-item text-center text-muted py-3">
                        <i class="fas fa-check-circle fa-2x mb-2 opacity-50"></i>
                        <p class="mb-0 small">Sin notificaciones nuevas</p>
                    </div>
                </li>
                @endforelse
                @if(Auth::user()->unreadNotifications->count() > 0)
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-center text-primary fw-semibold" href="#">
                        <i class="fas fa-eye me-1"></i> Ver todas
                    </a>
                </li>
                @endif
            </ul>
        </li>

        <!-- User Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle fa-lg"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                @can('ver-perfil')
                <li>
                    <a class="dropdown-item" href="{{ route('profile.index') }}">
                        <i class="fas fa-cog me-2 text-primary"></i>Configuraciones
                    </a>
                </li>
                @endcan
                @can('ver-registro-actividad')
                <li>
                    <a class="dropdown-item" href="{{ route('activityLog.index') }}">
                        <i class="fas fa-history me-2 text-info"></i>Registro de actividad
                    </a>
                </li>
                @endcan
                <li><hr class="dropdown-divider" /></li>
                <li>
                    <a class="dropdown-item text-danger" href="{{ route('logout') }}">
                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</nav>
