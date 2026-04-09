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
        <a href="{{ route('gastos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nuevo Gasto
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Cards resumen --}}
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <small class="text-muted d-block">Hoy</small>
                    <strong class="fs-5 text-danger">${{ number_format($totalHoy, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <small class="text-muted d-block">Este mes</small>
                    <strong class="fs-5 text-warning">${{ number_format($totalMes, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <small class="text-muted d-block">Total</small>
                    <strong class="fs-5 text-dark">${{ number_format($totalAll, 0, ',', '.') }}</strong>
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
                                {{ $gasto->metodo_pago ?? '—' }}
                            </td>
                            <td class="text-end fw-semibold text-danger">
                                ${{ number_format($gasto->monto, 0, ',', '.') }}
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @if ($gasto->comprobante_path)
                                    <a href="{{ Storage::url($gasto->comprobante_path) }}"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="Ver comprobante">
                                        <i class="fas fa-paperclip"></i>
                                    </a>
                                    @endif
                                    <form action="{{ route('gastos.destroy', $gasto->id) }}" method="POST"
                                        onsubmit="return confirm('¿Eliminar este gasto?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay gastos registrados.</td>
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
</script>
@endpush
