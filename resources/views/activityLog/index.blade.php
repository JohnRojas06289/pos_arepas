@extends('layouts.app')

@section('title', 'Registro de Actividad')

@push('css')
<style>
    .module-tab { cursor: pointer; font-size: 0.82rem; padding: 5px 14px; border-radius: 20px; border: 1px solid var(--border-color, #dee2e6); background: transparent; transition: all .18s; white-space: nowrap; }
    .module-tab.active { background: var(--color-primary, #C8553D); color: #fff; border-color: var(--color-primary, #C8553D); }
    .module-tab:not(.active):hover { background: var(--color-primary-subtle, #fce8e4); }
    .badge-module { font-size: 0.72rem; padding: 3px 8px; border-radius: 10px; }
    .action-icon { width: 30px; height: 30px; border-radius: 6px; border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-2 pt-3">

    {{-- Encabezado --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0" style="font-weight:800;color:var(--text-primary);">
                <i class="fas fa-history me-2" style="color:var(--color-primary);"></i>
                Registro de Actividad
            </h4>
            <div style="font-size:0.82rem;color:var(--text-secondary);">
                Historial completo de operaciones del sistema
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('activityLog.index') }}" id="filterForm">
                <input type="hidden" name="modulo" id="modulo_input" value="{{ $modulo }}">
                <div class="row g-2 align-items-end">

                    {{-- Fechas --}}
                    <div class="col-auto">
                        <label class="form-label mb-0 small">Desde</label>
                        <input type="date" name="desde" class="form-control form-control-sm" value="{{ $desde }}">
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-0 small">Hasta</label>
                        <input type="date" name="hasta" class="form-control form-control-sm" value="{{ $hasta }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-secondary">
                            <i class="fas fa-filter me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('activityLog.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Últimos 30 días</a>
                    </div>
                    <div class="col-auto ms-auto text-muted small align-self-center">
                        {{ $activityLogs->total() }} registro(s)
                    </div>
                </div>

                {{-- Tabs de módulo --}}
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="button"
                        class="module-tab {{ $modulo === 'todos' ? 'active' : '' }}"
                        onclick="setModulo('todos')">
                        Todos
                    </button>
                    @foreach($modulos as $mod)
                    <button type="button"
                        class="module-tab {{ $modulo === $mod ? 'active' : '' }}"
                        onclick="setModulo('{{ $mod }}')">
                        {{ $mod }}
                    </button>
                    @endforeach
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="font-size:0.82rem;background:var(--table-header-bg, #f8f9fa);">
                        <tr>
                            <th class="ps-3">Fecha</th>
                            <th>Usuario</th>
                            <th>Módulo</th>
                            <th>Acción</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody style="font-size:0.85rem;">
                        @forelse ($activityLogs as $log)
                        <tr>
                            <td class="ps-3" style="white-space:nowrap;color:var(--text-secondary);">
                                {{ $log->created_at_formatted }}
                            </td>
                            <td>
                                <span style="font-weight:500;">{{ $log->user->name ?? '—' }}</span>
                            </td>
                            <td>
                                @php
                                    $colors = [
                                        'Ventas'               => 'success',
                                        'Compras'              => 'primary',
                                        'Inventario'           => 'warning',
                                        'Productos'            => 'info',
                                        'Clientes'             => 'secondary',
                                        'Proveedores'          => 'dark',
                                        'Cajas'                => 'danger',
                                        'Usuarios'             => 'secondary',
                                        'Roles'                => 'dark',
                                        'Registro de actividad'=> 'secondary',
                                    ];
                                    $color = $colors[$log->module] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }} badge-module">{{ $log->module ?? '—' }}</span>
                            </td>
                            <td>{{ $log->action }}</td>
                            <td class="text-end pe-3">
                                <div class="d-flex justify-content-end gap-1">

                                    {{-- Ver detalle --}}
                                    <a href="{{ route('activityLog.show', $log->id) }}"
                                        class="action-icon"
                                        style="background:var(--color-info-subtle,#e8f4fd);color:var(--color-info,#5B9BD5);"
                                        title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- Revertir venta --}}
                                    @if($log->isVentaLog())
                                    @can('revertir-venta')
                                    <form action="{{ route('activityLog.reverseVenta', $log->id) }}" method="POST"
                                          onsubmit="return confirm('¿Revertir esta venta? Se restaurará el inventario.')">
                                        @csrf
                                        <button type="submit"
                                            class="action-icon"
                                            style="background:var(--color-warning-subtle,#fff8e1);color:var(--color-warning,#F5A623);"
                                            title="Revertir venta">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                    @endcan
                                    @endif

                                    {{-- Eliminar --}}
                                    @can('eliminar-registro-actividad')
                                    <form action="{{ route('activityLog.destroy', $log->id) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar este registro de actividad?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="action-icon"
                                            style="background:var(--color-danger-subtle,#fde8ea);color:var(--color-danger,#E74C5E);"
                                            title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan

                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5" style="color:var(--text-muted);">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                No hay registros para el período y módulo seleccionados
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($activityLogs->hasPages())
        <div class="card-footer py-2">
            {{ $activityLogs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('js')
<script>
function setModulo(modulo) {
    document.getElementById('modulo_input').value = modulo;
    document.getElementById('filterForm').submit();
}
</script>
@endpush
