@extends('layouts.app')

@section('title', 'Cierre de Inventario')

@push('css')
<style>
    .tabla-inventario td,
    .tabla-inventario th { vertical-align: middle; }

    .badge-diferencia { font-size: .8rem; min-width: 70px; display: inline-block; text-align: center; }

    .resumen-panel {
        position: sticky;
        bottom: 0;
        z-index: 100;
        background: var(--bg-card, #fff);
        border-top: 2px solid var(--border-color, #dee2e6);
        padding: 1rem;
        box-shadow: 0 -4px 12px rgba(0,0,0,.1);
    }

    .resumen-panel .kpi-card {
        border-radius: .5rem;
        padding: .75rem 1rem;
    }

    .lista-diferencias {
        max-height: 120px;
        overflow-y: auto;
        font-size: .82rem;
    }

    .input-fisica {
        max-width: 90px;
    }

    @media (max-width: 767px) {
        .tabla-inventario { font-size: .8rem; }
        .input-fisica { max-width: 70px; }
        .resumen-panel { display: none; }
        .resumen-panel.visible { display: block; }
    }

    #btnToggleResumen {
        display: none;
    }

    @media (max-width: 767px) {
        #btnToggleResumen { display: inline-flex; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-2 pb-5">

    {{-- Breadcrumb --}}
    <x-breadcrumb.template>
        <x-breadcrumb.item :href="route('panel')" content="Inicio" />
        <x-breadcrumb.item :href="route('cajas.index')" content="Cajas" />
        <x-breadcrumb.item active='true' content="Cierre de Inventario" />
    </x-breadcrumb.template>

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h1 class="mb-0"><i class="fas fa-boxes me-2"></i>Cierre de Inventario</h1>
            <small class="text-muted">
                Caja aperturada el {{ $caja->fecha_apertura }}
                @if($caja->nombre) — {{ $caja->nombre }} @endif
            </small>
        </div>
        <a href="{{ route('cajas.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    {{-- Alerta cierre anterior --}}
    @if($ultimoCierre)
    <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <span>
            <i class="fas fa-info-circle me-1"></i>
            Ya existe un cierre de inventario para esta caja guardado el
            <strong>{{ $ultimoCierre->created_at->format('d/m/Y H:i') }}</strong>
            por <strong>{{ $ultimoCierre->user->name ?? '—' }}</strong>.
        </span>
        <a href="{{ route('cierre-inventario.show', $ultimoCierre->id) }}"
           class="btn btn-sm btn-info text-white">
            <i class="fas fa-eye me-1"></i> Ver cierre
        </a>
    </div>
    @endif

    {{-- Formulario --}}
    <form id="formCierre"
          action="{{ route('cajas.cierre-inventario.store', $caja->id) }}"
          method="POST">
        @csrf

        <div class="card mb-3">
            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                <span><i class="fas fa-table me-1"></i> Conteo de Inventario</span>
                <small class="text-muted">(deja en blanco si no contaste ese producto)</small>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <input
                        type="text"
                        id="buscadorProducto"
                        class="form-control form-control-sm"
                        placeholder="Buscar producto..."
                        style="max-width:200px"
                        autocomplete="off"
                    >
                    <button type="button" id="btnToggleResumen" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-chart-bar me-1"></i> Diferencias
                    </button>
                </div>
            </div>
            <div class="card-body p-0 p-md-2">
                <div class="table-responsive">
                    <table class="table table-sm table-striped tabla-inventario mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:36px">#</th>
                                <th>Producto</th>
                                <th class="text-center" style="width:100px">En sistema</th>
                                <th class="text-center" style="width:110px">Físico</th>
                                <th class="text-center" style="width:110px">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productos as $index => $producto)
                            @php $sistema = $producto->inventario->cantidad ?? 0; @endphp
                            <tr>
                                <td class="text-muted">
                                    {{ $index + 1 }}
                                    {{-- Hidden fields dentro del <tr> para HTML válido --}}
                                    <input type="hidden" name="items[{{ $index }}][producto_id]"      value="{{ $producto->id }}">
                                    <input type="hidden" name="items[{{ $index }}][nombre]"           value="{{ $producto->nombre }}">
                                    <input type="hidden" name="items[{{ $index }}][cantidad_sistema]" value="{{ $sistema }}">
                                </td>
                                <td>{{ $producto->nombre }}</td>
                                <td class="text-center fw-semibold">{{ $sistema }}</td>
                                <td class="text-center">
                                    <input
                                        type="number"
                                        class="form-control form-control-sm input-fisica text-center mx-auto"
                                        name="items[{{ $index }}][cantidad_fisica]"
                                        min="0"
                                        placeholder="—"
                                        data-sistema="{{ $sistema }}"
                                        data-nombre="{{ $producto->nombre }}"
                                        data-index="{{ $index }}"
                                        autocomplete="off"
                                    >
                                </td>
                                <td class="text-center">
                                    <span id="badge-{{ $index }}" class="badge badge-diferencia bg-secondary">—</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay productos activos registrados.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </form>

    {{-- Panel resumen sticky --}}
    <div class="resumen-panel">
        <div class="row g-2">
            {{-- Faltantes --}}
            <div class="col-12 col-md-6">
                <div class="kpi-card border border-danger h-100">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="kpi-icon text-danger"><i class="fas fa-arrow-down"></i></span>
                        <span class="kpi-label fw-semibold text-danger">Faltantes</span>
                        <span class="ms-auto kpi-value text-danger fw-bold" id="totalFaltantes">0 und.</span>
                    </div>
                    <div class="lista-diferencias" id="listaFaltantes">
                        <span class="text-muted fst-italic">Sin diferencias</span>
                    </div>
                </div>
            </div>
            {{-- Sobrantes --}}
            <div class="col-12 col-md-6">
                <div class="kpi-card border border-success h-100">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="kpi-icon text-success"><i class="fas fa-arrow-up"></i></span>
                        <span class="kpi-label fw-semibold text-success">Sobrantes</span>
                        <span class="ms-auto kpi-value text-success fw-bold" id="totalSobrantes">0 und.</span>
                    </div>
                    <div class="lista-diferencias" id="listaSobrantes">
                        <span class="text-muted fst-italic">Sin diferencias</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Botón enviar --}}
    <div class="d-flex justify-content-end mt-3 pb-3 gap-2">
        <a href="{{ route('cajas.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-times me-1"></i> Cancelar
        </a>
        <button type="submit" form="formCierre" class="btn btn-success btn-lg" id="btnEnviar">
            <i class="fas fa-paper-plane me-2"></i> Enviar cierre
        </button>
    </div>

</div>
@endsection

@push('js')
<script>
(function () {
    const inputs = document.querySelectorAll('.input-fisica');

    function actualizarResumen() {
        const faltantes  = [];
        const sobrantes  = [];

        inputs.forEach(function (input) {
            const idx     = input.dataset.index;
            const sistema = parseInt(input.dataset.sistema, 10);
            const nombre  = input.dataset.nombre;
            const badge   = document.getElementById('badge-' + idx);
            const val     = input.value.trim();

            if (val === '') {
                badge.className = 'badge badge-diferencia bg-secondary';
                badge.textContent = '—';
                return;
            }

            const fisica = parseInt(val, 10);
            const diff   = fisica - sistema;

            if (diff === 0) {
                badge.className = 'badge badge-diferencia bg-success';
                badge.textContent = 'OK';
            } else if (diff > 0) {
                badge.className = 'badge badge-diferencia bg-success';
                badge.textContent = '+' + diff;
                sobrantes.push({ nombre: nombre, diff: diff });
            } else {
                badge.className = 'badge badge-diferencia bg-danger';
                badge.textContent = diff;
                faltantes.push({ nombre: nombre, diff: Math.abs(diff) });
            }
        });

        // Totales
        const totalF = faltantes.reduce(function (s, x) { return s + x.diff; }, 0);
        const totalS = sobrantes.reduce(function (s, x) { return s + x.diff; }, 0);

        document.getElementById('totalFaltantes').textContent = totalF + ' und.';
        document.getElementById('totalSobrantes').textContent = totalS + ' und.';

        // Lista faltantes
        const listaF = document.getElementById('listaFaltantes');
        if (faltantes.length === 0) {
            listaF.innerHTML = '<span class="text-muted fst-italic">Sin diferencias</span>';
        } else {
            listaF.innerHTML = faltantes.map(function (x) {
                return '<div class="text-danger"><i class="fas fa-minus-circle fa-xs me-1"></i>' + x.diff + ' und. de <strong>' + x.nombre + '</strong></div>';
            }).join('');
        }

        // Lista sobrantes
        const listaS = document.getElementById('listaSobrantes');
        if (sobrantes.length === 0) {
            listaS.innerHTML = '<span class="text-muted fst-italic">Sin diferencias</span>';
        } else {
            listaS.innerHTML = sobrantes.map(function (x) {
                return '<div class="text-success"><i class="fas fa-plus-circle fa-xs me-1"></i>' + x.diff + ' und. de <strong>' + x.nombre + '</strong></div>';
            }).join('');
        }
    }

    inputs.forEach(function (input) {
        input.addEventListener('input', actualizarResumen);
        input.addEventListener('change', actualizarResumen);
    });

    // ── Buscador de productos ──────────────────────────────────────────────
    document.getElementById('buscadorProducto').addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('.tabla-inventario tbody tr').forEach(function (tr) {
            const nombre = tr.querySelector('td:nth-child(2)');
            if (!nombre) return;
            tr.style.display = nombre.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // ── Toggle panel diferencias en mobile ────────────────────────────────
    document.getElementById('btnToggleResumen').addEventListener('click', function () {
        const panel = document.querySelector('.resumen-panel');
        panel.classList.toggle('visible');
        const visible = panel.classList.contains('visible');
        this.innerHTML = visible
            ? '<i class="fas fa-times me-1"></i> Ocultar'
            : '<i class="fas fa-chart-bar me-1"></i> Diferencias';
    });

    // ── Protección doble submit ────────────────────────────────────────────
    document.getElementById('formCierre').addEventListener('submit', function () {
        const btn = document.getElementById('btnEnviar');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Guardando...';
    });
})();
</script>
@endpush
