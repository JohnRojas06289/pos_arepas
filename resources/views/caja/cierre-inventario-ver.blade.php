@extends('layouts.app')

@section('title', 'Ver Cierre de Inventario')

@push('css')
<style>
    .tabla-cierre td,
    .tabla-cierre th { vertical-align: middle; }
    .badge-diferencia { font-size: .8rem; min-width: 70px; display: inline-block; text-align: center; }
    .lista-diferencias { font-size: .85rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-2">

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
                Guardado el {{ $cierre->created_at->format('d/m/Y H:i') }}
                por <strong>{{ $cierre->user->name ?? '—' }}</strong>
            </small>
        </div>
        <a href="{{ route('cajas.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    {{-- Tabla de items --}}
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i> Detalle del conteo
        </div>
        <div class="card-body p-0 p-md-2">
            <div class="table-responsive">
                <table class="table table-sm table-striped tabla-cierre mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:36px">#</th>
                            <th>Producto</th>
                            <th class="text-center" style="width:100px">En sistema</th>
                            <th class="text-center" style="width:100px">Físico</th>
                            <th class="text-center" style="width:110px">Diferencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $contados = collect($cierre->items)->filter(fn($i) => $i['cantidad_fisica'] !== null);
                            $numero   = 1;
                        @endphp
                        @forelse($contados as $item)
                        @php
                            $diff = $item['diferencia'];
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $numero++ }}</td>
                            <td>{{ $item['nombre'] }}</td>
                            <td class="text-center">{{ $item['cantidad_sistema'] }}</td>
                            <td class="text-center fw-semibold">{{ $item['cantidad_fisica'] }}</td>
                            <td class="text-center">
                                @if($diff === 0 || $diff === null)
                                    <span class="badge badge-diferencia bg-success">OK</span>
                                @elseif($diff > 0)
                                    <span class="badge badge-diferencia bg-success">+{{ $diff }}</span>
                                @else
                                    <span class="badge badge-diferencia bg-danger">{{ $diff }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No se contó ningún producto en este cierre.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Resumen faltantes / sobrantes --}}
    @php
        $faltantes = collect($cierre->items)->filter(fn($i) => $i['diferencia'] !== null && $i['diferencia'] < 0);
        $sobrantes = collect($cierre->items)->filter(fn($i) => $i['diferencia'] !== null && $i['diferencia'] > 0);
        $totalFaltantes = $faltantes->sum(fn($i) => abs($i['diferencia']));
        $totalSobrantes = $sobrantes->sum(fn($i) => $i['diferencia']);
    @endphp

    <div class="row g-3 mb-4">
        {{-- Faltantes --}}
        <div class="col-12 col-md-6">
            <div class="kpi-card border border-danger h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="kpi-icon text-danger"><i class="fas fa-arrow-down"></i></span>
                    <span class="kpi-label fw-semibold text-danger">Faltantes</span>
                    <span class="ms-auto kpi-value text-danger fw-bold">{{ $totalFaltantes }} und.</span>
                </div>
                <div class="lista-diferencias">
                    @forelse($faltantes as $item)
                    <div class="text-danger">
                        <i class="fas fa-minus-circle fa-xs me-1"></i>
                        {{ abs($item['diferencia']) }} und. de <strong>{{ $item['nombre'] }}</strong>
                    </div>
                    @empty
                    <span class="text-muted fst-italic">Sin faltantes</span>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sobrantes --}}
        <div class="col-12 col-md-6">
            <div class="kpi-card border border-success h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="kpi-icon text-success"><i class="fas fa-arrow-up"></i></span>
                    <span class="kpi-label fw-semibold text-success">Sobrantes</span>
                    <span class="ms-auto kpi-value text-success fw-bold">{{ $totalSobrantes }} und.</span>
                </div>
                <div class="lista-diferencias">
                    @forelse($sobrantes as $item)
                    <div class="text-success">
                        <i class="fas fa-plus-circle fa-xs me-1"></i>
                        {{ $item['diferencia'] }} und. de <strong>{{ $item['nombre'] }}</strong>
                    </div>
                    @empty
                    <span class="text-muted fst-italic">Sin sobrantes</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if($faltantes->isEmpty() && $sobrantes->isEmpty())
    <div class="alert alert-success text-center">
        <i class="fas fa-check-circle fa-lg me-2"></i>
        <strong>Todo en orden</strong> — no se registraron diferencias en este cierre.
    </div>
    @endif

</div>
@endsection
