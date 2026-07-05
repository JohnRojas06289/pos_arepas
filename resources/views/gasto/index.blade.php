@extends('layouts.app')

@section('title', 'Gastos')

@push('css')
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
<style>
    .badge-categoria { font-size: .75rem; }
    .filtro-cat.active, .filtro-periodo.active {
        background: var(--color-primary); color: #fff; border-color: var(--color-primary);
    }
    @media (max-width: 767px) {
        #tablaGastos { font-size: .82rem; }
        #tablaGastos td, #tablaGastos th { padding: .35rem .4rem; }
    }
    .items-resumen { font-size: .78rem; color: var(--bs-secondary); max-width: 180px; }
    .items-resumen .badge-qty { font-size: .7rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-2">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <div>
            <h1>Gastos</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Gastos</li>
            </ol>
        </div>
        @can('crear-gasto')
        <a href="{{ route('gastos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nuevo Gasto
        </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Card resumen --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <small class="text-muted d-block">Gastos de hoy</small>
                    <strong class="fs-5 text-danger">${{ number_format($totalHoy, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <span><i class="fas fa-table me-1"></i> Historial de Gastos</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                {{-- Período --}}
                <div class="d-flex gap-1 flex-wrap">
                    <button class="btn btn-sm btn-outline-secondary filtro-periodo active" data-periodo="todos">Todos</button>
                    <button class="btn btn-sm btn-outline-secondary filtro-periodo" data-periodo="hoy">Hoy</button>
                    <button class="btn btn-sm btn-outline-secondary filtro-periodo" data-periodo="semana">Semana</button>
                    <button class="btn btn-sm btn-outline-secondary filtro-periodo" data-periodo="mes">Mes</button>
                </div>
                {{-- Categoría --}}
                <div class="d-flex gap-1 flex-wrap">
                    <button class="btn btn-sm btn-outline-secondary filtro-cat active" data-cat="todas">Todas</button>
                    @foreach ($categorias as $cat)
                    <button class="btn btn-sm btn-outline-{{ $cat->color() }} filtro-cat" data-cat="{{ $cat->value }}">
                        {{ $cat->label() }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card-body p-0 p-md-3">
            <div class="table-responsive">
                <table id="tablaGastos" class="table table-sm table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th class="d-none d-md-table-cell">Items</th>
                            <th class="d-none d-md-table-cell">Método</th>
                            <th class="text-end">Monto</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($gastos as $gasto)
                        <tr data-fecha="{{ $gasto->fecha->format('Y-m-d') }}" data-cat="{{ $gasto->categoria->value }}">
                            <td>
                                <div class="fw-semibold text-capitalize">
                                    {{ \Carbon\Carbon::parse($gasto->fecha)->locale('es_CO')->isoFormat('ddd') }}
                                </div>
                                <small class="text-muted">{{ $gasto->fecha_formateada }}</small>
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $gasto->categoria->color() }} badge-categoria">
                                    {{ $gasto->categoria->label() }}
                                </span>
                            </td>
                            <td>
                                {{ $gasto->descripcion }}
                                @if ($gasto->notas)
                                <small class="text-muted d-block">{{ Str::limit($gasto->notas, 40) }}</small>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if ($gasto->compra && $gasto->compra->productos->isNotEmpty())
                                    <div class="items-resumen">
                                        <span class="badge bg-secondary badge-qty me-1">{{ $gasto->compra->productos->count() }} items</span>
                                        <span class="text-muted">
                                            {{ $gasto->compra->productos->take(2)->pluck('nombre')->implode(', ') }}{{ $gasto->compra->productos->count() > 2 ? '...' : '' }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell">
                                {{ $gasto->metodo_pago ?? '—' }}
                            </td>
                            <td class="text-end fw-semibold text-danger">
                                ${{ number_format($gasto->monto, 0, ',', '.') }}
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary btn-ver-detalle"
                                        title="Ver detalle"
                                        data-id="{{ $gasto->id }}"
                                        data-fecha="{{ $gasto->fecha_formateada }}"
                                        data-dia="{{ \Carbon\Carbon::parse($gasto->fecha)->locale('es_CO')->isoFormat('dddd D [de] MMMM YYYY') }}"
                                        data-categoria="{{ $gasto->categoria->label() }}"
                                        data-categoria-color="{{ $gasto->categoria->color() }}"
                                        data-descripcion="{{ $gasto->descripcion ?? '—' }}"
                                        data-metodo="{{ $gasto->metodo_pago ?? '—' }}"
                                        data-monto="{{ number_format($gasto->monto, 0, ',', '.') }}"
                                        data-notas="{{ $gasto->notas ?? '' }}"
                                        data-comprobante="{{ $gasto->comprobante_path ? Storage::url($gasto->comprobante_path) : '' }}"
                                        data-productos="{{ $gasto->compra ? $gasto->compra->productos->map(fn($p) => ['nombre' => $p->nombre, 'cantidad' => $p->pivot->cantidad, 'precio' => $p->pivot->precio_compra, 'subtotal' => $p->pivot->cantidad * $p->pivot->precio_compra])->toJson() : '[]' }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if ($gasto->comprobante_path)
                                    <a href="{{ Storage::url($gasto->comprobante_path) }}"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="Ver comprobante">
                                        <i class="fas fa-paperclip"></i>
                                    </a>
                                    @endif
                                    @can('eliminar-gasto')
                                    <form action="{{ route('gastos.destroy', $gasto->id) }}" method="POST"
                                        onsubmit="return confirm('¿Eliminar este gasto?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay gastos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="sinResultados" class="text-center text-muted py-4" style="display:none;">
                <i class="fas fa-search fa-2x mb-2"></i>
                <p>No hay gastos en este período o categoría.</p>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detalle Gasto --}}
<div class="modal fade" id="modalDetalleGasto" tabindex="-1" aria-labelledby="modalDetalleGastoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleGastoLabel">
                    <i class="fas fa-receipt me-2"></i> Detalle del Gasto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Fecha y categoría --}}
                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                        <div id="detalleDia" class="fw-semibold text-capitalize"></div>
                        <small id="detalleFecha" class="text-muted"></small>
                    </div>
                    <span id="detalleCategoriaBadge" class="badge fs-6"></span>
                </div>

                <hr class="my-2">

                {{-- Info principal --}}
                <div class="row g-3 mb-3">
                    <div class="col-12 col-sm-6">
                        <small class="text-muted d-block">Descripción</small>
                        <span id="detalleDescripcion" class="fw-medium"></span>
                    </div>
                    <div class="col-6 col-sm-3">
                        <small class="text-muted d-block">Método de pago</small>
                        <span id="detalleMetodo"></span>
                    </div>
                    <div class="col-6 col-sm-3 text-sm-end">
                        <small class="text-muted d-block">Monto total</small>
                        <span id="detalleMonto" class="fw-bold text-danger fs-5"></span>
                    </div>
                </div>

                {{-- Notas --}}
                <div id="detalleNotasRow" class="mb-3" style="display:none;">
                    <small class="text-muted d-block">Notas</small>
                    <p id="detalleNotas" class="mb-0 fst-italic text-secondary"></p>
                </div>

                {{-- Comprobante --}}
                <div id="detalleComprobanteRow" class="mb-3" style="display:none;">
                    <small class="text-muted d-block mb-1">Comprobante</small>
                    <a id="detalleComprobanteLink" href="#" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-paperclip me-1"></i> Ver comprobante
                    </a>
                </div>

                {{-- Tabla de productos (SURTIDO) --}}
                <div id="detalleProductosSection" style="display:none;">
                    <hr class="my-3">
                    <h6 class="mb-2"><i class="fas fa-boxes me-1"></i> Productos comprados</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio unitario</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="detalleProductosTbody"></tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Total</td>
                                    <td class="text-end text-danger" id="detalleProductosTotal"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
let periodoActivo = 'todos';
let catActiva     = 'todas';

document.querySelectorAll('.filtro-periodo').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filtro-periodo').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        periodoActivo = this.dataset.periodo;
        aplicarFiltros();
    });
});

document.querySelectorAll('.filtro-cat').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filtro-cat').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        catActiva = this.dataset.cat;
        aplicarFiltros();
    });
});

function aplicarFiltros() {
    const rows = document.querySelectorAll('#tablaGastos tbody tr[data-fecha]');
    const hoy  = new Date(); hoy.setHours(0,0,0,0);

    const inicioSemana = new Date(hoy);
    const ds = hoy.getDay() === 0 ? 6 : hoy.getDay() - 1;
    inicioSemana.setDate(hoy.getDate() - ds);

    let visibles = 0;
    rows.forEach(row => {
        const fecha  = new Date(row.dataset.fecha + 'T00:00:00');
        const cat    = row.dataset.cat;

        let pasaPeriodo = true;
        if (periodoActivo === 'hoy') {
            pasaPeriodo = fecha.toDateString() === hoy.toDateString();
        } else if (periodoActivo === 'semana') {
            pasaPeriodo = fecha >= inicioSemana && fecha <= hoy;
        } else if (periodoActivo === 'mes') {
            pasaPeriodo = fecha.getMonth() === hoy.getMonth() && fecha.getFullYear() === hoy.getFullYear();
        }

        const pasaCat = catActiva === 'todas' || cat === catActiva;
        const visible = pasaPeriodo && pasaCat;
        row.style.display = visible ? '' : 'none';
        if (visible) visibles++;
    });

    document.getElementById('sinResultados').style.display = visibles === 0 ? 'block' : 'none';
}

// Modal detalle
document.querySelectorAll('.btn-ver-detalle').forEach(btn => {
    btn.addEventListener('click', function () {
        const d = this.dataset;

        document.getElementById('detalleDia').textContent         = d.dia;
        document.getElementById('detalleFecha').textContent       = d.fecha;
        document.getElementById('detalleDescripcion').textContent = d.descripcion;
        document.getElementById('detalleMetodo').textContent      = d.metodo;
        document.getElementById('detalleMonto').textContent       = '$' + d.monto;

        const badge = document.getElementById('detalleCategoriaBadge');
        badge.className = 'badge fs-6 text-bg-' + d.categoriaColor;
        badge.textContent = d.categoria;

        const notasRow = document.getElementById('detalleNotasRow');
        if (d.notas && d.notas.trim() !== '') {
            document.getElementById('detalleNotas').textContent = d.notas;
            notasRow.style.display = '';
        } else {
            notasRow.style.display = 'none';
        }

        const comprobanteRow = document.getElementById('detalleComprobanteRow');
        if (d.comprobante && d.comprobante.trim() !== '') {
            document.getElementById('detalleComprobanteLink').href = d.comprobante;
            comprobanteRow.style.display = '';
        } else {
            comprobanteRow.style.display = 'none';
        }

        const productosSection = document.getElementById('detalleProductosSection');
        const tbody = document.getElementById('detalleProductosTbody');
        const totalEl = document.getElementById('detalleProductosTotal');

        let productos = [];
        try { productos = JSON.parse(d.productos || '[]'); } catch (e) { productos = []; }

        if (productos.length > 0) {
            tbody.innerHTML = '';
            let total = 0;
            productos.forEach(p => {
                total += parseFloat(p.subtotal) || 0;
                tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td>${p.nombre}</td>
                        <td class="text-center">${p.cantidad}</td>
                        <td class="text-end">$${Number(p.precio).toLocaleString('es-CO')}</td>
                        <td class="text-end">$${Number(p.subtotal).toLocaleString('es-CO')}</td>
                    </tr>
                `);
            });
            totalEl.textContent = '$' + total.toLocaleString('es-CO');
            productosSection.style.display = '';
        } else {
            productosSection.style.display = 'none';
        }

        new bootstrap.Modal(document.getElementById('modalDetalleGasto')).show();
    });
});
</script>
@endpush
