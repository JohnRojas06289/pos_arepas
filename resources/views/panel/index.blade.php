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
                    <h6 class="m-0 font-weight-bold text-primary">Ventas del Día por Cliente</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Total Ventas</th>
                                    <th>Transacciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventasPorCliente as $clienteId => $ventas)
                                <tr>
                                    <td>{{ $ventas->first()->cliente ? $ventas->first()->cliente->persona->razon_social : 'Cliente General' }}</td>
                                    <td class="fw-bold text-success">${{ number_format($ventas->sum('total'), 2) }}</td>
                                    <td>{{ $ventas->count() }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info text-white" onclick="showTransacciones('{{ $clienteId ? $clienteId : 'general' }}', '{{ $ventas->first()->cliente ? addslashes($ventas->first()->cliente->persona->razon_social) : 'Cliente General' }}')">
                                            <i class="fas fa-eye me-1"></i> Ver Transacciones
                                        </button>
                                        <div id="data_transacciones_{{ $clienteId ? $clienteId : 'general' }}" class="d-none">
                                            @foreach($ventas as $v)
                                                <div class="tx-item" data-fecha="{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i') }}" data-total="{{ number_format($v->total, 2) }}" data-vendedor="{{ $v->user->name }}" data-metodo="{{ $v->metodo_pago }}"></div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay ventas registradas el día de hoy</td>
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

<!-- Modal para ver las transacciones del cliente -->
<div class="modal fade" id="transaccionesModal" tabindex="-1" aria-labelledby="transaccionesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="transaccionesModalLabel"><i class="fas fa-list me-2"></i>Transacciones de <span id="modalClientName"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Total</th>
                                <th>Método</th>
                                <th>Vendedor</th>
                            </tr>
                        </thead>
                        <tbody id="transaccionesModalBody">
                            <!-- Las filas se llenan via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    function showTransacciones(clienteId, clienteNombre) {
        document.getElementById('modalClientName').textContent = clienteNombre;
        
        let txContainer = document.getElementById('data_transacciones_' + clienteId);
        let tbody = document.getElementById('transaccionesModalBody');
        tbody.innerHTML = '';
        
        let items = txContainer.querySelectorAll('.tx-item');
        items.forEach(function(item) {
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.getAttribute('data-fecha')}</td>
                <td class="fw-bold">$${item.getAttribute('data-total')}</td>
                <td><span class="badge bg-secondary" style="font-size: 0.7em;">${item.getAttribute('data-metodo')}</span></td>
                <td><small class="text-muted">${item.getAttribute('data-vendedor')}</small></td>
            `;
            tbody.appendChild(tr);
        });
        
        var myModal = new bootstrap.Modal(document.getElementById('transaccionesModal'));
        myModal.show();
    }
</script>
@endpush

