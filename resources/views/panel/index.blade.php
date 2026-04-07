@extends('layouts.app')

@section('title', 'Inicio')

@push('css')
<style>
    .kpi-value { font-family: 'JetBrains Mono', monospace; }
    .quick-link-card {
        display: block;
        padding: 1rem;
        border-radius: 16px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        box-shadow: var(--card-shadow);
        text-decoration: none;
        color: inherit;
        height: 100%;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .quick-link-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--card-shadow-hover);
        border-color: var(--color-primary);
        color: inherit;
    }
    .quick-link-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        margin-bottom: .75rem;
        background: var(--color-primary-subtle);
        color: var(--color-primary);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-2 pt-3">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0" style="font-weight:800;color:var(--text-primary);">
                <i class="fas fa-house me-2" style="color:var(--color-primary);"></i>
                Inicio operativo
            </h4>
            <div style="font-size:0.82rem;color:var(--text-secondary);margin-top:2px;">
                {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
            </div>
        </div>
        @can('ver-estadisticas')
        <a href="{{ route('admin.estadisticas') }}" class="btn btn-outline-primary">
            <i class="fas fa-chart-pie me-1"></i>
            Ver estadísticas
        </a>
        @else
        @hasrole('administrador')
        <a href="{{ route('admin.estadisticas') }}" class="btn btn-outline-primary">
            <i class="fas fa-chart-pie me-1"></i>
            Ver estadísticas
        </a>
        @endhasrole
        @endcan
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="kpi-card success h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-label">Ventas de hoy</div>
                        <div class="kpi-value" style="font-size:1.4rem;">${{ number_format($ventasHoy, 0, ',', '.') }}</div>
                        <div class="small fw-semibold" style="color:var(--color-success);">Ingreso registrado hoy</div>
                    </div>
                    <div class="kpi-icon success ms-3"><i class="fas fa-cash-register"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card primary h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-label">Transacciones</div>
                        <div class="kpi-value" style="font-size:1.4rem;">{{ number_format($transaccionesHoy, 0, ',', '.') }}</div>
                        <div class="small text-muted fw-semibold">Ventas realizadas hoy</div>
                    </div>
                    <div class="kpi-icon primary ms-3"><i class="fas fa-receipt"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card info h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-label">Clientes atendidos</div>
                        <div class="kpi-value" style="font-size:1.4rem;">{{ number_format($clientesAtendidos, 0, ',', '.') }}</div>
                        <div class="small fw-semibold" style="color:var(--color-info);">Clientes distintos del día</div>
                    </div>
                    <div class="kpi-icon info ms-3"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card warning h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-label">Ticket promedio</div>
                        <div class="kpi-value" style="font-size:1.4rem;">${{ number_format($ticketPromedio, 0, ',', '.') }}</div>
                        <div class="small fw-semibold" style="color:var(--color-warning);">Promedio por venta</div>
                    </div>
                    <div class="kpi-icon warning ms-3"><i class="fas fa-chart-simple"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-bolt me-2" style="color:var(--color-primary);"></i>
                    Accesos rápidos
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @can('crear-venta')
                        <div class="col-12">
                            <a href="{{ route('ventas.create') }}" class="quick-link-card">
                                <div class="quick-link-icon"><i class="fas fa-cash-register"></i></div>
                                <div class="fw-bold mb-1">Punto de venta</div>
                                <div class="small text-muted">Abrir caja de cobro y registrar una venta nueva.</div>
                            </a>
                        </div>
                        @endcan

                        @can('ver-venta')
                        <div class="col-12">
                            <a href="{{ route('ventas.index') }}" class="quick-link-card">
                                <div class="quick-link-icon"><i class="fas fa-receipt"></i></div>
                                <div class="fw-bold mb-1">Historial de ventas</div>
                                <div class="small text-muted">Consultar comprobantes, montos y detalle de ventas.</div>
                            </a>
                        </div>
                        @endcan

                        @can('ver-caja')
                        <div class="col-12">
                            <a href="{{ route('cajas.index') }}" class="quick-link-card">
                                <div class="quick-link-icon"><i class="fas fa-money-bill-wave"></i></div>
                                <div class="fw-bold mb-1">Cajas</div>
                                <div class="small text-muted">Abrir, revisar o cerrar la caja activa.</div>
                            </a>
                        </div>
                        @endcan

                        @hasrole('administrador')
                        <div class="col-12">
                            <a href="{{ route('admin.estadisticas') }}" class="quick-link-card">
                                <div class="quick-link-icon"><i class="fas fa-chart-pie"></i></div>
                                <div class="fw-bold mb-1">Estadísticas</div>
                                <div class="small text-muted">Ver análisis, tendencias y comportamiento del negocio.</div>
                            </a>
                        </div>
                        @endhasrole
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <i class="fas fa-clock-rotate-left me-2" style="color:var(--color-primary);"></i>
                        Últimas ventas de hoy
                    </div>
                    <span class="badge" style="background:var(--color-primary-subtle);color:var(--color-primary);">
                        {{ $ultimasVentas->count() }} {{ $ultimasVentas->count() === 1 ? 'registro' : 'registros' }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Cliente</th>
                                    <th>Método</th>
                                    <th>Total</th>
                                    <th>Vendedor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ultimasVentas as $venta)
                                <tr>
                                    <td class="fw-semibold">{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('H:i') }}</td>
                                    <td>{{ $venta->cliente?->persona?->razon_social ?? 'Cliente general' }}</td>
                                    <td>
                                        <span class="badge" style="background:var(--color-info-subtle);color:var(--color-info);">
                                            {{ $venta->metodo_pago }}
                                        </span>
                                    </td>
                                    <td class="fw-bold" style="color:var(--color-success);">
                                        ${{ number_format($venta->total, 0, ',', '.') }}
                                    </td>
                                    <td>{{ $venta->user?->name ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="fas fa-mug-hot fa-2x mb-2 d-block" style="color:var(--color-primary);opacity:0.35;"></i>
                                        <span style="color:var(--text-muted);">Todavía no hay ventas registradas hoy.</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
