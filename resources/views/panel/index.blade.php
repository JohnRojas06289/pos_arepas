@extends('layouts.app')

@section('title','Panel - Ventas de Hoy')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
<style>
    /* Modern KPI Cards */
    .kpi-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border-left: 4px solid;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        border-radius: 0 0 0 100%;
        opacity: 0.1;
    }
    .kpi-card:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15); }
    .kpi-card.primary { border-left-color: #f59e0b; }
    .kpi-card.primary::before { background: #f59e0b; }
    .kpi-card.success { border-left-color: #059669; }
    .kpi-card.success::before { background: #059669; }
    .kpi-card.info { border-left-color: #0ea5e9; }
    .kpi-card.info::before { background: #0ea5e9; }
    .kpi-card.warning { border-left-color: #eab308; }
    .kpi-card.warning::before { background: #eab308; }

    .kpi-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }
    .kpi-value {
        font-size: 2rem;
        font-weight: 900;
        color: #111827;
        line-height: 1;
        margin-bottom: 0.5rem;
        animation: countUp 0.5s ease-out;
    }
    .kpi-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: white;
    }
    .kpi-icon.primary { background: var(--color-primary); }
    .kpi-icon.success { background: var(--color-success); }
    .kpi-icon.info { background: var(--color-info); }
    .kpi-icon.warning { background: var(--color-warning); }

    @keyframes countUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-2 mt-4">

    <!-- Desglose de Ventas de HOY -->
    <div class="row g-3 mb-4">
        <!-- Total Hoy -->
        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card success">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Total Hoy</div>
                        <div class="kpi-value" style="font-size:1.5rem;">${{ number_format($ventasHoy, 0, ',', '.') }}</div>
                        <div class="small text-success fw-semibold"><i class="fas fa-calendar-day me-1"></i>Ventas del día</div>
                    </div>
                    <div class="kpi-icon success"><i class="fas fa-cash-register"></i></div>
                </div>
            </div>
        </div>

        <!-- Efectivo -->
        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Efectivo</div>
                        <div class="kpi-value" style="font-size:1.5rem;">${{ number_format($ventasEfectivo, 0, ',', '.') }}</div>
                        <div class="small text-muted fw-semibold"><i class="fas fa-money-bill me-1"></i>En efectivo</div>
                    </div>
                    <div class="kpi-icon primary"><i class="fas fa-money-bill-wave"></i></div>
                </div>
            </div>
        </div>

        <!-- Nequi -->
        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card info">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Nequi</div>
                        <div class="kpi-value" style="font-size:1.5rem;">${{ number_format($ventasNequi, 0, ',', '.') }}</div>
                        <div class="small text-info fw-semibold"><i class="fas fa-mobile-alt me-1"></i>Transferencia</div>
                    </div>
                    <div class="kpi-icon info"><i class="fas fa-mobile-alt"></i></div>
                </div>
            </div>
        </div>

        <!-- Daviplata -->
        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Daviplata</div>
                        <div class="kpi-value" style="font-size:1.5rem;">${{ number_format($ventasDaviplata, 0, ',', '.') }}</div>
                        <div class="small text-warning fw-semibold"><i class="fas fa-university me-1"></i>Transferencia</div>
                    </div>
                    <div class="kpi-icon warning"><i class="fas fa-university"></i></div>
                </div>
            </div>
        </div>

        <!-- Fiado -->
        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card" style="border-left-color:#dc2626;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Fiado</div>
                        <div class="kpi-value" style="font-size:1.5rem;">${{ number_format($ventasFiado, 0, ',', '.') }}</div>
                        <div class="small text-danger fw-semibold"><i class="fas fa-handshake me-1"></i>Pendiente</div>
                    </div>
                    <div class="kpi-icon" style="background:#dc2626;"><i class="fas fa-handshake"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas Transacciones -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Últimas 5 Transacciones</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Fecha y Hora</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Método</th>
                                    <th>Vendedor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ultimasVentas as $venta)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($venta->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>{{ $venta->cliente ? $venta->cliente->persona->razon_social : 'Cliente General' }}</td>
                                    <td class="fw-bold text-success">${{ number_format($venta->total, 2) }}</td>
                                    <td><span class="badge bg-secondary">{{ $venta->metodo_pago }}</span></td>
                                    <td>{{ $venta->user->name }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@endpush
