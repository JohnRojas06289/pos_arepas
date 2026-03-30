@extends('layouts.app')

@section('title', 'Detalle de Actividad')

@push('css')
<style>
    .data-key   { font-size:0.78rem; color:var(--text-secondary); text-transform:uppercase; letter-spacing:.05em; font-weight:600; }
    .data-value { font-size:0.9rem; word-break:break-word; }
    .section-title { font-size:0.82rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-secondary); border-bottom:1px solid var(--border-color,#dee2e6); padding-bottom:6px; margin-bottom:14px; }
</style>
@endpush

@section('content')
<div class="container-fluid px-2 pt-3">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('activityLog.index') }}">Registro de Actividad</a></li>
            <li class="breadcrumb-item active">Detalle</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">

        {{-- Columna izquierda: metadata + datos del log --}}
        <div class="col-lg-4">

            {{-- Metadata del registro --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="section-title">Registro de actividad</div>
                    <div class="mb-3">
                        <div class="data-key">Módulo</div>
                        <div class="data-value">
                            @php
                                $colors = ['Ventas'=>'success','Compras'=>'primary','Inventario'=>'warning','Productos'=>'info','Clientes'=>'secondary','Proveedores'=>'dark','Cajas'=>'danger'];
                                $color = $colors[$log->module] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ $log->module ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="data-key">Acción</div>
                        <div class="data-value fw-semibold">{{ $log->action }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="data-key">Usuario</div>
                        <div class="data-value">{{ $log->user->name ?? '—' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="data-key">Fecha y hora</div>
                        <div class="data-value">{{ $log->created_at_formatted }}</div>
                    </div>
                    @if($log->ip_address)
                    <div class="mb-0">
                        <div class="data-key">IP</div>
                        <div class="data-value" style="font-family:monospace;">{{ $log->ip_address }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Datos JSON del log --}}
            @if($log->data)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="section-title">Datos registrados</div>
                    @foreach($log->data as $key => $value)
                        @if(!in_array($key, ['venta_id','compra_id','arrayidproducto','arraycantidad','arrayprecioventa','arraypreciocompra','arrayfechavencimiento']))
                        <div class="mb-2">
                            <div class="data-key">{{ str_replace('_', ' ', $key) }}</div>
                            <div class="data-value">
                                @if(is_array($value))
                                    <code style="font-size:0.78rem;">{{ json_encode($value) }}</code>
                                @elseif($value === null)
                                    <span class="text-muted">—</span>
                                @else
                                    {{ $value }}
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Botones de acción --}}
            <div class="card">
                <div class="card-body d-flex flex-column gap-2">
                    <div class="section-title mb-2">Acciones</div>

                    {{-- Editar venta --}}
                    @if($log->isVentaLog() && $venta && $log->getVentaId() && !$venta->revertida)
                    <button type="button" class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#editVentaModal">
                        <i class="fas fa-edit me-1"></i> Editar venta
                    </button>
                    @endif

                    {{-- Revertir venta --}}
                    @if($log->isVentaLog() && $venta && !$venta->revertida)
                    @can('revertir-venta')
                    <form action="{{ route('activityLog.reverseVenta', $log->id) }}" method="POST"
                          onsubmit="return confirm('¿Revertir esta venta? Se restaurará el inventario de todos los productos.')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-warning w-100">
                            <i class="fas fa-undo me-1"></i> Revertir venta
                        </button>
                    </form>
                    @endcan
                    @elseif($log->isVentaLog() && $venta && $venta->revertida)
                    <div class="alert alert-warning py-2 mb-0" style="font-size:0.82rem;">
                        <i class="fas fa-info-circle me-1"></i> Esta venta ya fue revertida
                    </div>
                    @endif

                    {{-- Eliminar log --}}
                    @can('eliminar-registro-actividad')
                    <form action="{{ route('activityLog.destroy', $log->id) }}" method="POST"
                          onsubmit="return confirm('¿Eliminar este registro de actividad? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                            <i class="fas fa-trash me-1"></i> Eliminar registro
                        </button>
                    </form>
                    @endcan

                    <a href="{{ route('activityLog.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left me-1"></i> Volver al listado
                    </a>
                </div>
            </div>

        </div>

        {{-- Columna derecha: detalle de la transacción --}}
        <div class="col-lg-8">

            {{-- Detalle de VENTA --}}
            @if($venta)
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <i class="fas fa-receipt me-2" style="color:var(--color-success);"></i>
                        <strong>Venta {{ $venta->numero_comprobante }}</strong>
                    </div>
                    @if($venta->revertida)
                    <span class="badge bg-warning text-dark">Revertida</span>
                    @else
                    <span class="badge bg-success">Activa</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <div class="data-key">Comprobante</div>
                            <div class="data-value">{{ $venta->comprobante?->nombre ?? 'N/A' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="data-key">Cliente</div>
                            <div class="data-value">{{ $venta->cliente?->persona?->razon_social ?? 'Cliente general' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="data-key">Vendedor</div>
                            <div class="data-value">{{ $venta->user?->name ?? '—' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="data-key">Método de pago</div>
                            <div class="data-value">{{ $venta->metodo_pago }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="data-key">Fecha y hora</div>
                            <div class="data-value">{{ $venta->fecha }} {{ $venta->hora }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="data-key">Estado de pago</div>
                            <div class="data-value">
                                @if($venta->pagado)
                                    <span class="badge bg-success">Pagado</span>
                                @else
                                    <span class="badge bg-danger">Pendiente — ${{ number_format($venta->saldo_pendiente, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Tabla de productos --}}
                    <div class="section-title">Productos</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-2">
                            <thead style="font-size:0.78rem;background:var(--table-header-bg,#f8f9fa);">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-end">Precio unit.</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody style="font-size:0.85rem;">
                                @foreach($venta->productos as $item)
                                <tr>
                                    <td>
                                        {{ $item->nombre }}
                                        @if($item->presentacione)
                                        <span class="text-muted">({{ $item->presentacione->sigla }})</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->pivot->cantidad }}</td>
                                    <td class="text-end">${{ number_format($item->pivot->precio_venta, 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">${{ number_format($item->pivot->cantidad * $item->pivot->precio_venta, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total</td>
                                    <td class="text-end fw-bold" style="color:var(--color-success);">
                                        ${{ number_format($venta->total, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Detalle de COMPRA --}}
            @if($compra)
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-shopping-cart me-2" style="color:var(--color-primary);"></i>
                    <strong>Compra {{ $compra->numero_comprobante ?? '—' }}</strong>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <div class="data-key">Proveedor</div>
                            <div class="data-value">{{ $compra->proveedore?->persona?->razon_social ?? '—' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="data-key">Método de pago</div>
                            <div class="data-value">{{ $compra->metodo_pago }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="data-key">Registrado por</div>
                            <div class="data-value">{{ $compra->user?->name ?? '—' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="data-key">Fecha</div>
                            <div class="data-value">{{ $compra->fecha }} {{ $compra->hora }}</div>
                        </div>
                    </div>

                    <div class="section-title">Productos recibidos</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead style="font-size:0.78rem;background:var(--table-header-bg,#f8f9fa);">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-end">Precio compra</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody style="font-size:0.85rem;">
                                @foreach($compra->productos as $item)
                                <tr>
                                    <td>{{ $item->nombre }}</td>
                                    <td class="text-center">{{ $item->pivot->cantidad }}</td>
                                    <td class="text-end">${{ number_format($item->pivot->precio_compra, 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">${{ number_format($item->pivot->cantidad * $item->pivot->precio_compra, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total</td>
                                    <td class="text-end fw-bold" style="color:var(--color-primary);">
                                        ${{ number_format($compra->total, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Si no hay transacción vinculada --}}
            @if(!$venta && !$compra)
            <div class="card">
                <div class="card-body text-center py-5" style="color:var(--text-muted);">
                    <i class="fas fa-file-alt fa-2x mb-2 d-block"></i>
                    <div style="font-size:0.9rem;">
                        Este registro no tiene una transacción vinculada directamente.<br>
                        Consulta los datos registrados en el panel izquierdo.
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

{{-- Modal: Editar venta --}}
@if($log->isVentaLog() && $venta && $log->getVentaId() && !$venta->revertida)
<div class="modal fade" id="editVentaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('activityLog.updateVenta', $log->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size:0.95rem;font-weight:700;">
                        <i class="fas fa-edit me-2"></i>Editar Venta {{ $venta->numero_comprobante }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2" style="font-size:0.82rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        Solo se pueden editar campos que no afectan el inventario. Para cambios en productos, revierte y crea una nueva venta.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Método de pago</label>
                        <select name="metodo_pago" class="form-select">
                            @foreach($metodosPago as $metodo)
                            <option value="{{ $metodo->value }}" {{ $venta->metodo_pago === $metodo->value ? 'selected' : '' }}>
                                {{ $metodo->value }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold">Cliente</label>
                        <select name="cliente_id" class="form-select">
                            <option value="">— Cliente general —</option>
                            @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ $venta->cliente_id === $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->persona->razon_social }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save me-1"></i>Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
