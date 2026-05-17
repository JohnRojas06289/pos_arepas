@extends('layouts.app')

@section('title', 'Mi Panel — Pedidos')

@push('css')
<style>
    .pedido-row { cursor: default; transition: background 0.15s; }
    .pedido-row:hover { background: var(--bg-hover, rgba(0,0,0,0.03)); }
    .badge-pendiente { background: var(--color-warning, #f59e0b); color: #fff; }
    .badge-tomado    { background: var(--color-success, #10b981); color: #fff; }
    .items-list { font-size: 0.82rem; color: var(--text-secondary); line-height: 1.6; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 pt-3">

    {{-- Encabezado --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0" style="font-weight:800;color:var(--text-primary);">
                <i class="fas fa-clipboard-list me-2" style="color:var(--color-primary);"></i>
                Mis pedidos de hoy
            </h4>
            <div style="font-size:0.82rem;color:var(--text-secondary);margin-top:2px;">
                {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                &mdash; {{ auth()->user()->name }}
            </div>
        </div>
        <a href="{{ route('pedidos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nuevo pedido
        </a>
    </div>

    {{-- KPIs --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card primary h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Total hoy</div>
                        <div class="kpi-value" style="font-size:1.4rem;">
                            ${{ number_format($totalHoy, 0, ',', '.') }}
                        </div>
                        <div class="small fw-semibold" style="color:var(--color-primary);">
                            {{ $pedidosHoy->count() }} pedido{{ $pedidosHoy->count() !== 1 ? 's' : '' }}
                        </div>
                    </div>
                    <div class="kpi-icon primary ms-3"><i class="fas fa-clipboard-list"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card warning h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Pendientes</div>
                        <div class="kpi-value" style="font-size:1.4rem;">
                            {{ $pendientes->count() }}
                        </div>
                        <div class="small fw-semibold" style="color:var(--color-warning);">
                            Por procesar
                        </div>
                    </div>
                    <div class="kpi-icon warning ms-3"><i class="fas fa-hourglass-half"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card success h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Tomados</div>
                        <div class="kpi-value" style="font-size:1.4rem;">
                            {{ $tomados->count() }}
                        </div>
                        <div class="small fw-semibold" style="color:var(--color-success);">
                            Procesados
                        </div>
                    </div>
                    <div class="kpi-icon success ms-3"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card danger h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Por cobrar</div>
                        <div class="kpi-value" style="font-size:1.4rem;">
                            ${{ number_format($totalPorCobrar, 0, ',', '.') }}
                        </div>
                        <div class="small fw-semibold" style="color:var(--color-danger);">
                            Pedidos pendientes
                        </div>
                    </div>
                    <div class="kpi-icon danger ms-3"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card accent h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Facturado</div>
                        <div class="kpi-value" style="font-size:1.4rem;">
                            ${{ number_format($totalFacturado, 0, ',', '.') }}
                        </div>
                        <div class="small fw-semibold" style="color:var(--color-accent-dark);">
                            Pedidos tomados
                        </div>
                    </div>
                    <div class="kpi-icon accent ms-3"><i class="fas fa-receipt"></i></div>
                </div>
            </div>
        </div>

    </div>

    {{-- Tabla de pedidos --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between py-3"
             style="background:var(--bg-card);border-bottom:1px solid var(--border-color);">
            <span style="font-weight:700;font-size:0.95rem;color:var(--text-primary);">
                <i class="fas fa-list me-2" style="color:var(--color-primary);"></i>
                Historial del día
            </span>
            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt me-1"></i> Actualizar
            </button>
        </div>

        <div class="card-body p-0">
            @if($pedidosHoy->isEmpty())
                <div class="text-center py-5" style="color:var(--text-secondary);">
                    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                    <p class="mb-0">Aún no has tomado pedidos hoy.</p>
                    <a href="{{ route('pedidos.create') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-1"></i> Tomar primer pedido
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:0.88rem;">
                        <thead style="background:var(--bg-hover,#f8f9fa);color:var(--text-secondary);font-size:0.78rem;text-transform:uppercase;letter-spacing:0.05em;">
                            <tr>
                                <th class="ps-4 py-3">#</th>
                                <th class="py-3">Hora</th>
                                <th class="py-3">Productos</th>
                                <th class="py-3 text-end">Total</th>
                                <th class="py-3 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedidosHoy as $pedido)
                            <tr class="pedido-row">
                                <td class="ps-4 py-3 fw-semibold" style="color:var(--text-secondary);">
                                    #{{ $pedido->id }}
                                </td>
                                <td class="py-3" style="color:var(--text-secondary);white-space:nowrap;">
                                    <i class="fas fa-clock me-1 opacity-50"></i>
                                    {{ $pedido->created_at->format('h:i a') }}
                                </td>
                                <td class="py-3">
                                    <div class="items-list">
                                        @foreach($pedido->items as $item)
                                            <span>
                                                {{ $item['cantidad'] ?? 1 }}×
                                                {{ $item['nombre'] ?? '—' }}
                                            </span>@if(!$loop->last),&nbsp;@endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-3 text-end fw-semibold" style="font-family:monospace;color:var(--text-primary);">
                                    ${{ number_format($pedido->total, 0, ',', '.') }}
                                </td>
                                <td class="py-3 text-center">
                                    @if($pedido->estado === 'pendiente')
                                        <span class="badge badge-pendiente rounded-pill px-3">
                                            <i class="fas fa-hourglass-half me-1"></i>Pendiente
                                        </span>
                                    @else
                                        <span class="badge badge-tomado rounded-pill px-3">
                                            <i class="fas fa-check me-1"></i>Tomado
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="background:var(--bg-hover,#f8f9fa);border-top:2px solid var(--border-color);">
                            <tr>
                                <td colspan="3" class="ps-4 py-3 fw-bold" style="color:var(--text-primary);">
                                    Total del día
                                </td>
                                <td class="py-3 text-end fw-bold" style="font-family:monospace;font-size:1rem;color:var(--text-primary);">
                                    ${{ number_format($totalHoy, 0, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
