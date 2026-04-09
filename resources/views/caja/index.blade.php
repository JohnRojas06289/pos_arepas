@extends('layouts.app')

@section('title','cajas')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
@endpush

@section('content')

<div class="container-fluid px-2">
    <h1 class="mt-1 text-center">Cajas</h1>

    <x-breadcrumb.template>
        <x-breadcrumb.item :href="route('panel')" content="Inicio" />
        <x-breadcrumb.item active='true' content="Cajas" />
    </x-breadcrumb.template>

    @can('aperturar-caja')
    <div class="mb-4">
        <a href="{{route('cajas.create')}}">
            <button type="button" class="btn btn-primary">Aperturar caja</button>
        </a>
    </div>
    @endcan


    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla cajas
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table-striped fs-6">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Saldo inicial</th>
                        <th>Saldo final</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cajas as $item)
                    <tr>
                        <td>
                            {{$item->nombre}}
                        </td>
                        <td>
                            <p class="fw-semibold mb-1">
                                <span class="m-1"><i class="fa-solid fa-calendar-days"></i></span>
                                {{$item->fecha_apertura}}
                            </p>
                            <p class="fw-semibold mb-0"><span class="m-1"><i class="fa-solid fa-clock"></i></span>
                                {{$item->hora_apertura}}
                            </p>
                        </td>
                        <td>
                            @if ($item->fecha_hora_cierre)
                            <p class="fw-semibold mb-1">
                                <span class="m-1"><i class="fa-solid fa-calendar-days"></i></span>
                                {{$item->fecha_cierre}}
                            </p>
                            <p class="fw-semibold mb-0"><span class="m-1"><i class="fa-solid fa-clock"></i></span>
                                {{$item->hora_cierre}}
                            </p>
                            @endif
                        </td>
                        <td>
                            {{number_format($item->saldo_inicial, 0, ',', '.')}}
                        </td>
                        <td>
                            {{number_format($item->saldo_final, 0, ',', '.')}}
                        </td>
                        <td>
                            <span class="badge rounded-pill {{ $item->estado == 1 ? 'text-bg-success' : 'text-bg-danger' }}">
                                {{$item->estado == 1 ? 'aperturada' : 'cerrada'}}</span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @can('ver-movimiento')
                                <form action="{{route('movimientos.index')}}" method="get">
                                    <input type="hidden" name="caja_id" value="{{$item->id}}">
                                    <button type="submit" class="btn btn-success">
                                        Ver
                                    </button>
                                </form>
                                @endcan

                                <button type="button" class="btn btn-outline-info"
                                    onclick="verResumen('{{ route('cajas.resumen', $item->id) }}', '{{ $item->fecha_apertura }}')">
                                    <i class="fas fa-chart-bar"></i> Resumen
                                </button>

                                @can('cerrar-caja')
                                @if ($item->estado == 1)
                                <button type="button" class="btn btn-danger"
                                    onclick="abrirCierre('{{ route('cajas.resumen', $item->id) }}', '{{ route('cajas.destroy', $item->id) }}', '{{ $item->fecha_apertura }}')">
                                    Cerrar
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
<script src="{{ asset('js/simple-datatables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
<script>
function fmt(n) {
    return '$' + Number(n).toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function buildResumenHtml(data) {
    const productos = data.por_producto;
    const metodos   = data.por_metodo;
    const total     = data.total_general;
    const numVentas = data.num_ventas;

    let rows = productos.length
        ? productos.map(p => `
            <tr>
                <td>${p.nombre}</td>
                <td class="text-center">${p.cantidad}</td>
                <td class="text-end">${fmt(p.total)}</td>
            </tr>`).join('')
        : '<tr><td colspan="3" class="text-center text-muted">Sin ventas registradas</td></tr>';

    return `
        <p class="text-muted mb-3"><i class="fas fa-receipt me-1"></i>${numVentas} venta(s) registradas</p>

        <h6 class="fw-bold mb-2"><i class="fas fa-box me-1"></i>Ventas por Producto</h6>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-striped table-bordered">
                <thead class="table-dark">
                    <tr><th>Producto</th><th class="text-center">Cantidad</th><th class="text-end">Total</th></tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>

        <h6 class="fw-bold mb-2"><i class="fas fa-money-bill-wave me-1"></i>Totales por Método de Pago</h6>
        <div class="row g-2 mb-3">
            <div class="col-4">
                <div class="card border-success text-center p-2">
                    <small class="text-muted">Efectivo</small>
                    <strong class="text-success">${fmt(metodos.EFECTIVO)}</strong>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-primary text-center p-2">
                    <small class="text-muted">Nequi</small>
                    <strong class="text-primary">${fmt(metodos.NEQUI)}</strong>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-warning text-center p-2">
                    <small class="text-muted">Daviplata</small>
                    <strong class="text-warning">${fmt(metodos.DAVIPLATA)}</strong>
                </div>
            </div>
        </div>

        <div class="alert alert-dark d-flex justify-content-between align-items-center">
            <strong><i class="fas fa-sigma me-1"></i>Total General</strong>
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


