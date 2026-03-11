@extends('layouts.app')

@section('title', 'Panel — Ventas de Hoy')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
<style>
    .kpi-value { font-family: 'JetBrains Mono', monospace; }
</style>
@endpush

@section('content')
<div class="container-fluid px-2 pt-3">

    {{-- Encabezado --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0" style="font-weight:800;color:var(--text-primary);">
                <i class="fas fa-chart-line me-2" style="color:var(--color-primary);"></i>
                Panel de hoy
            </h4>
            <div style="font-size:0.82rem;color:var(--text-secondary);margin-top:2px;">
                {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
            </div>
        </div>
        @can('crear-venta')
        <a href="{{ route('ventas.create') }}" class="btn btn-primary">
            <i class="fas fa-cash-register"></i>
            <span>Nueva Venta</span>
        </a>
        @endcan
    </div>

    {{-- KPIs del día --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card success h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Total hoy</div>
                        <div class="kpi-value" style="font-size:1.4rem;">${{ number_format($ventasHoy, 0, ',', '.') }}</div>
                        <div class="small fw-semibold" style="color:var(--color-success);">
                            <i class="fas fa-calendar-day me-1"></i>Ventas del día
                        </div>
                    </div>
                    <div class="kpi-icon success ms-3"><i class="fas fa-cash-register"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card primary h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Efectivo</div>
                        <div class="kpi-value" style="font-size:1.4rem;">${{ number_format($ventasEfectivo, 0, ',', '.') }}</div>
                        <div class="small text-muted fw-semibold"><i class="fas fa-money-bill me-1"></i>En efectivo</div>
                    </div>
                    <div class="kpi-icon primary ms-3"><i class="fas fa-money-bill-wave"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card info h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Nequi</div>
                        <div class="kpi-value" style="font-size:1.4rem;">${{ number_format($ventasNequi, 0, ',', '.') }}</div>
                        <div class="small fw-semibold" style="color:var(--color-info);">
                            <i class="fas fa-mobile-alt me-1"></i>Transferencia
                        </div>
                    </div>
                    <div class="kpi-icon info ms-3"><i class="fas fa-mobile-alt"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card warning h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Daviplata</div>
                        <div class="kpi-value" style="font-size:1.4rem;">${{ number_format($ventasDaviplata, 0, ',', '.') }}</div>
                        <div class="small fw-semibold" style="color:var(--color-warning);">
                            <i class="fas fa-university me-1"></i>Transferencia
                        </div>
                    </div>
                    <div class="kpi-icon warning ms-3"><i class="fas fa-university"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card danger h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Fiado</div>
                        <div class="kpi-value" style="font-size:1.4rem;">${{ number_format($ventasFiado, 0, ',', '.') }}</div>
                        <div class="small fw-semibold" style="color:var(--color-danger);">
                            <i class="fas fa-handshake me-1"></i>Pendiente
                        </div>
                    </div>
                    <div class="kpi-icon danger ms-3"><i class="fas fa-handshake"></i></div>
                </div>
            </div>
        </div>

    </div>

    {{-- Tabla: Ventas del día por cliente --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <i class="fas fa-users me-2" style="color:var(--color-primary);"></i>
                <span>Ventas del día por cliente</span>
            </div>
            <span class="badge" style="background:var(--color-primary-subtle);color:var(--color-primary);">
                {{ collect($ventasPorCliente)->count() }} {{ collect($ventasPorCliente)->count() === 1 ? 'cliente' : 'clientes' }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Transacciones</th>
                            <th style="width:100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventasPorCliente as $clienteId => $ventas)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:30px;height:30px;border-radius:50%;background:var(--color-primary-subtle);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-user" style="color:var(--color-primary);font-size:0.7rem;"></i>
                                    </div>
                                    <span style="font-weight:500;">
                                        {{ $ventas->first()->cliente?->persona?->razon_social ?? 'Cliente General' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="text-cop fw-bold" style="color:var(--color-success);">
                                    ${{ number_format($ventas->sum('total'), 0, ',', '.') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background:var(--color-info-subtle);color:var(--color-info);">
                                    {{ $ventas->count() }}
                                </span>
                            </td>
                            <td>
                                <button type="button"
                                    class="btn btn-sm"
                                    style="background:var(--color-info-subtle);color:var(--color-info);border:none;height:30px;"
                                    onclick="showTransacciones('{{ $clienteId ? $clienteId : 'general' }}', '{{ addslashes($ventas->first()->cliente?->persona?->razon_social ?? 'Cliente General') }}')">
                                    <i class="fas fa-eye"></i>
                                    <span class="d-none d-md-inline ms-1">Ver</span>
                                </button>
                                <div id="data_transacciones_{{ $clienteId ? $clienteId : 'general' }}" class="d-none">
                                    @foreach($ventas as $v)
                                    <div class="tx-item"
                                         data-fecha="{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i') }}"
                                         data-total="{{ number_format($v->total, 0, ',', '.') }}"
                                         data-vendedor="{{ $v->user?->name ?? 'N/A' }}"
                                         data-metodo="{{ $v->metodo_pago }}"></div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="fas fa-mug-hot fa-2x mb-2 d-block" style="color:var(--color-primary);opacity:0.35;"></i>
                                <span style="color:var(--text-muted);">No hay ventas registradas hoy</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Modal: transacciones del cliente --}}
<div class="modal fade" id="transaccionesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--color-primary);">
                <h5 class="modal-title" style="color:#fff;font-size:0.95rem;font-weight:700;">
                    <i class="fas fa-list me-2"></i>Transacciones — <span id="modalClientName"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha y hora</th>
                                <th>Total</th>
                                <th>Método</th>
                                <th>Vendedor</th>
                            </tr>
                        </thead>
                        <tbody id="transaccionesModalBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
function showTransacciones(clienteId, clienteNombre) {
    document.getElementById('modalClientName').textContent = clienteNombre;
    var tbody = document.getElementById('transaccionesModalBody');
    tbody.innerHTML = '';
    var items = document.getElementById('data_transacciones_' + clienteId).querySelectorAll('.tx-item');
    var colors = { efectivo: 'success', nequi: 'info', daviplata: 'warning', fiado: 'danger', tarjeta: 'primary' };
    items.forEach(function(item) {
        var metodo = (item.getAttribute('data-metodo') || '').toLowerCase();
        var color  = colors[metodo] || 'secondary';
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td style="font-size:0.82rem;">' + item.getAttribute('data-fecha') + '</td>' +
            '<td class="fw-bold text-cop" style="color:var(--color-success);">$' + item.getAttribute('data-total') + '</td>' +
            '<td><span class="badge bg-' + color + '">' + item.getAttribute('data-metodo') + '</span></td>' +
            '<td style="font-size:0.82rem;color:var(--text-secondary);">' + item.getAttribute('data-vendedor') + '</td>';
        tbody.appendChild(tr);
    });
    new bootstrap.Modal(document.getElementById('transaccionesModal')).show();
}
</script>
@endpush
