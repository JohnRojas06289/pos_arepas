@extends('layouts.app')

@section('title','cajas')

@push('css')
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
<style>
    #tablaCajas td, #tablaCajas th { vertical-align: middle; }
    .filtro-btn.active { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }
    .badge-estado { font-size: .75rem; }
    /* Columnas compactas en móvil */
    @media (max-width: 767px) {
        #tablaCajas { font-size: .78rem; }
        #tablaCajas td, #tablaCajas th { padding: .35rem .4rem; }
        .col-apertura { width: 90px; max-width: 90px; }
        .col-estado   { width: 60px; max-width: 60px; }
        .col-acciones { width: 80px; max-width: 80px; }
        .btn-resumen-sm { font-size: .7rem; padding: .25rem .4rem; white-space: nowrap; }
    }
</style>
@endpush

@section('content')

<div class="container-fluid px-2">
    <h1 class="mt-1 text-center">Cajas</h1>

    <x-breadcrumb.template>
        <x-breadcrumb.item :href="route('panel')" content="Inicio" />
        <x-breadcrumb.item active='true' content="Cajas" />
    </x-breadcrumb.template>

    @can('aperturar-caja')
    <div class="mb-3">
        <a href="{{route('cajas.create')}}">
            <button type="button" class="btn btn-primary">Aperturar caja</button>
        </a>
    </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <span><i class="fas fa-table me-1"></i> Tabla cajas</span>
                <!-- Orden -->
                <button id="btnOrden" class="btn btn-sm btn-outline-dark" onclick="toggleOrden()">
                    <i class="fas fa-sort-amount-down me-1" id="iconOrden"></i><span id="textoOrden">Reciente primero</span>
                </button>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <!-- Período -->
                <div class="d-flex gap-1 flex-wrap">
                    <button class="btn btn-sm btn-outline-secondary filtro-periodo active" data-periodo="todos">Todos</button>
                    <button class="btn btn-sm btn-outline-secondary filtro-periodo" data-periodo="hoy">Hoy</button>
                    <button class="btn btn-sm btn-outline-secondary filtro-periodo" data-periodo="semana">Semana</button>
                    <button class="btn btn-sm btn-outline-secondary filtro-periodo" data-periodo="mes">Mes</button>
                </div>
                <!-- Estado -->
                <div class="d-flex gap-1 flex-wrap">
                    <button class="btn btn-sm btn-outline-secondary filtro-estado active" data-estado="todas">Todas</button>
                    <button class="btn btn-sm btn-outline-success filtro-estado" data-estado="1">Abiertas</button>
                    <button class="btn btn-sm btn-outline-danger filtro-estado" data-estado="0">Cerradas</button>
                </div>
            </div>
        </div>
        <div class="card-body p-0 p-md-3">
            <div class="table-responsive">
                <table id="tablaCajas" class="table table-sm table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="col-apertura">Apertura</th>
                            <th class="d-none d-md-table-cell">Cierre</th>
                            <th class="d-none d-md-table-cell">Saldo inicial</th>
                            <th class="d-none d-md-table-cell">Saldo final</th>
                            <th class="col-estado">Estado</th>
                            <th class="col-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cajas as $item)
                        @php
                            $apertura = \Carbon\Carbon::parse($item->fecha_hora_apertura);
                        @endphp
                        <tr data-fecha="{{ $apertura->format('Y-m-d') }}" data-estado="{{ $item->estado ? '1' : '0' }}">
                            <td class="col-apertura">
                                {{-- Día abreviado en móvil, completo en desktop --}}
                                <div class="fw-semibold text-capitalize d-none d-md-block">{{ $apertura->locale('es_CO')->isoFormat('dddd') }}</div>
                                <div class="fw-semibold text-capitalize d-md-none">{{ $apertura->locale('es_CO')->isoFormat('ddd') }}</div>
                                <small class="text-muted">{{ $item->fecha_apertura }}</small>
                                <small class="text-muted d-block"><i class="fa-solid fa-clock fa-xs me-1"></i>{{ $item->hora_apertura }}</small>
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if ($item->fecha_hora_cierre)
                                @php $cierre = \Carbon\Carbon::parse($item->fecha_hora_cierre); @endphp
                                <div class="fw-semibold text-capitalize">{{ $cierre->locale('es_CO')->isoFormat('dddd') }}</div>
                                <small class="text-muted">{{ $item->fecha_cierre }}</small>
                                <small class="text-muted d-block"><i class="fa-solid fa-clock fa-xs me-1"></i>{{ $item->hora_cierre }}</small>
                                @else
                                <small class="text-muted">—</small>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell">
                                ${{ number_format($item->saldo_inicial, 0, ',', '.') }}
                            </td>
                            <td class="d-none d-md-table-cell">
                                ${{ number_format($item->saldo_final, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge rounded-pill badge-estado {{ $item->estado == 1 ? 'text-bg-success' : 'text-bg-danger' }}">
                                    {{ $item->estado == 1 ? 'Abierta' : 'Cerrada' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-info btn-resumen-sm"
                                        onclick="verResumen('{{ route('cajas.resumen', $item->id) }}', '{{ $item->fecha_apertura }}')">
                                        <i class="fas fa-chart-bar"></i> Resumen
                                    </button>
                                    @can('cerrar-caja')
                                    @if ($item->estado == 1)
                                    <button type="button" class="btn btn-sm btn-danger btn-resumen-sm"
                                        onclick="abrirCierre('{{ route('cajas.resumen', $item->id) }}', '{{ route('cajas.destroy', $item->id) }}', '{{ $item->fecha_apertura }}')">
                                        <i class="fas fa-lock fa-xs"></i> Cerrar
                                    </button>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div id="sinResultados" class="text-center text-muted py-4" style="display:none;">
                <i class="fas fa-search fa-2x mb-2"></i>
                <p>No hay cajas en este período.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Resumen de Caja -->
<div class="modal fade" id="resumenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-chart-bar me-2"></i>Resumen de Caja — <span id="resumenFecha"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resumenBody">
                <div class="text-center py-4"><div class="spinner-border text-info"></div><p class="mt-2">Cargando...</p></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cierre de Caja (con resumen) -->
<div class="modal fade" id="cierreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-cash-register me-2"></i>Cerrar Caja — <span id="cierreFecha"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="cierreBody">
                <div class="text-center py-4"><div class="spinner-border text-danger"></div><p class="mt-2">Cargando resumen...</p></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="cierreForm" method="post">
                    @method('DELETE')
                    @csrf
                    <button type="submit" class="btn btn-danger"><i class="fas fa-lock me-1"></i>Confirmar Cierre</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
// ── Estado del filtro ─────────────────────────────────────────────────────────
let periodoActivo = 'todos';
let estadoActivo  = 'todas';
let ordenDesc     = true; // true = reciente primero

// Período
document.querySelectorAll('.filtro-periodo').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filtro-periodo').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        periodoActivo = this.dataset.periodo;
        aplicarFiltros();
    });
});

// Estado
document.querySelectorAll('.filtro-estado').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filtro-estado').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        estadoActivo = this.dataset.estado;
        aplicarFiltros();
    });
});

// Orden
function toggleOrden() {
    ordenDesc = !ordenDesc;
    document.getElementById('iconOrden').className  = ordenDesc
        ? 'fas fa-sort-amount-down me-1'
        : 'fas fa-sort-amount-up me-1';
    document.getElementById('textoOrden').textContent = ordenDesc
        ? 'Reciente primero'
        : 'Antiguo primero';
    aplicarFiltros();
}

function aplicarFiltros() {
    const tbody  = document.querySelector('#tablaCajas tbody');
    const rows   = Array.from(tbody.querySelectorAll('tr'));
    const hoy    = new Date();
    hoy.setHours(0, 0, 0, 0);

    const inicioSemana = new Date(hoy);
    const diaSemana    = hoy.getDay() === 0 ? 6 : hoy.getDay() - 1;
    inicioSemana.setDate(hoy.getDate() - diaSemana);

    // 1. Filtrar
    let visibles = 0;
    rows.forEach(row => {
        const fecha   = new Date(row.dataset.fecha + 'T00:00:00');
        const estado  = row.dataset.estado;

        let pasaPeriodo = true;
        if (periodoActivo === 'hoy') {
            pasaPeriodo = fecha.toDateString() === hoy.toDateString();
        } else if (periodoActivo === 'semana') {
            pasaPeriodo = fecha >= inicioSemana && fecha <= hoy;
        } else if (periodoActivo === 'mes') {
            pasaPeriodo = fecha.getMonth() === hoy.getMonth() && fecha.getFullYear() === hoy.getFullYear();
        }

        const pasaEstado = estadoActivo === 'todas' || estado === estadoActivo;

        const visible = pasaPeriodo && pasaEstado;
        row.style.display = visible ? '' : 'none';
        if (visible) visibles++;
    });

    // 2. Ordenar (solo las visibles, reinsertando en el DOM)
    const visibleRows = rows.filter(r => r.style.display !== 'none');
    visibleRows.sort((a, b) => {
        const fa = new Date(a.dataset.fecha + 'T00:00:00');
        const fb = new Date(b.dataset.fecha + 'T00:00:00');
        return ordenDesc ? fb - fa : fa - fb;
    });
    visibleRows.forEach(row => tbody.appendChild(row));

    document.getElementById('sinResultados').style.display = visibles === 0 ? 'block' : 'none';
}

// ── Resumen ───────────────────────────────────────────────────────────────────
function fmt(n) {
    return '$' + Number(n).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

const METODO_CONFIG = {
    EFECTIVO:  { label: 'Efectivo',  color: 'success' },
    NEQUI:     { label: 'Nequi',     color: 'primary' },
    DAVIPLATA: { label: 'Daviplata', color: 'warning' },
    TARJETA:   { label: 'Tarjeta',   color: 'info'    },
    FIADO:     { label: 'Fiado',     color: 'secondary' },
};

function buildResumenHtml(data) {
    const productos  = data.por_producto;
    const metodos    = data.por_metodo;   // objeto dinámico { EFECTIVO: x, NEQUI: y, ... }
    const total      = data.total_general;
    const numVentas  = data.num_ventas;
    const totalFiado = data.total_fiado  ?? 0;
    const numFiado   = data.num_fiado    ?? 0;

    // Tabla de productos
    let rows = productos.length
        ? productos.map(p => `
            <tr>
                <td>${p.nombre}</td>
                <td class="text-center">${p.cantidad}</td>
                <td class="text-end">${fmt(p.total)}</td>
            </tr>`).join('')
        : '<tr><td colspan="3" class="text-center text-muted">Sin ventas registradas</td></tr>';

    // Cards de métodos de pago — dinámicas
    const metodosKeys = Object.keys(metodos);
    let metodosHtml = '';
    if (metodosKeys.length === 0) {
        metodosHtml = '<p class="text-muted">Sin métodos registrados.</p>';
    } else {
        const colSize = metodosKeys.length <= 3 ? Math.floor(12 / metodosKeys.length) : 4;
        metodosHtml = '<div class="row g-2 mb-3">' +
            metodosKeys.map(key => {
                const cfg    = METODO_CONFIG[key] ?? { label: key, color: 'secondary' };
                return `
                <div class="col-${colSize}">
                    <div class="card border-${cfg.color} text-center p-2">
                        <small class="text-muted">${cfg.label}</small>
                        <strong class="text-${cfg.color}">${fmt(metodos[key])}</strong>
                    </div>
                </div>`;
            }).join('') +
        '</div>';
    }

    // Bloque fiado (solo si hay)
    const fiadoHtml = numFiado > 0 ? `
        <div class="alert alert-warning d-flex justify-content-between align-items-center mb-3">
            <span><i class="fas fa-clock me-1"></i><strong>Fiado pendiente</strong> (${numFiado} venta(s))</span>
            <strong>${fmt(totalFiado)}</strong>
        </div>` : '';

    return `
        <p class="text-muted mb-3"><i class="fas fa-receipt me-1"></i>${numVentas} venta(s) cobrada(s)</p>

        <h6 class="fw-bold mb-2"><i class="fas fa-box me-1"></i>Ventas por Producto</h6>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-striped table-bordered">
                <thead class="table-dark">
                    <tr><th>Producto</th><th class="text-center">Cantidad</th><th class="text-end">Total</th></tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>

        <h6 class="fw-bold mb-2"><i class="fas fa-money-bill-wave me-1"></i>Por Método de Pago</h6>
        ${metodosHtml}

        ${fiadoHtml}

        <div class="alert alert-dark d-flex justify-content-between align-items-center">
            <strong><i class="fas fa-sigma me-1"></i>Total Cobrado</strong>
            <strong class="fs-5">${fmt(total)}</strong>
        </div>`;
}

async function fetchResumen(resumenUrl) {
    const res = await fetch(resumenUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error('Error al cargar el resumen');
    return res.json();
}

function verResumen(resumenUrl, fecha) {
    document.getElementById('resumenFecha').textContent = fecha;
    document.getElementById('resumenBody').innerHTML =
        '<div class="text-center py-4"><div class="spinner-border text-info"></div><p class="mt-2">Cargando...</p></div>';
    new bootstrap.Modal(document.getElementById('resumenModal')).show();

    fetchResumen(resumenUrl)
        .then(data => { document.getElementById('resumenBody').innerHTML = buildResumenHtml(data); })
        .catch(() => { document.getElementById('resumenBody').innerHTML = '<div class="alert alert-danger">Error al cargar el resumen.</div>'; });
}

function abrirCierre(resumenUrl, actionUrl, fecha) {
    document.getElementById('cierreFecha').textContent = fecha;
    document.getElementById('cierreForm').action = actionUrl;
    document.getElementById('cierreBody').innerHTML =
        '<div class="text-center py-4"><div class="spinner-border text-danger"></div><p class="mt-2">Cargando resumen...</p></div>';
    new bootstrap.Modal(document.getElementById('cierreModal')).show();

    fetchResumen(resumenUrl)
        .then(data => { document.getElementById('cierreBody').innerHTML = buildResumenHtml(data); })
        .catch(() => { document.getElementById('cierreBody').innerHTML = '<div class="alert alert-warning">No se pudo cargar el resumen, pero puedes cerrar la caja de todos modos.</div>'; });
}
</script>
@endpush
