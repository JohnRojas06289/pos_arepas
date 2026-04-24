@extends('layouts.app')

@section('title','Inventario')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<style>
    .badge-ventas, .badge-compras {
        font-size: 0.95em;
        min-width: 32px;
        display: inline-block;
    }
    .btn-detalle {
        padding: 2px 8px;
        font-size: 0.78em;
    }
    #ventasDetalleModal .table th,
    #comprasDetalleModal .table th {
        font-size: 0.85em;
        white-space: nowrap;
    }
    #ventasDetalleModal .table td,
    #comprasDetalleModal .table td {
        font-size: 0.85em;
    }
    .periodo-select {
        max-width: 180px;
    }
    .vendidos-cell, .comprados-cell {
        text-align: center;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')

<div class="container-fluid px-2">
    <h1 class="mt-1 text-center">Inventario</h1>

    <x-breadcrumb.template>
        <x-breadcrumb.item :href="route('panel')" content="Inicio" />
        <x-breadcrumb.item active='true' content="Inventario" />
    </x-breadcrumb.template>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-end g-2">
                <form action="{{ route('inventario.index') }}" method="GET" class="row align-items-end g-2 w-100">
                <div class="col-md-3">
                    <label for="categoria_id" class="form-label">Filtrar por Categoría</label>
                    <select name="categoria_id" id="categoria_id" class="form-select">
                        <option value="">Todas las categorías</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->caracteristica->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="periodo" class="form-label">Ventas por periodo</label>
                    <select name="periodo" id="periodo" class="form-select periodo-select">
                        <option value="hoy"    {{ $periodo == 'hoy'    ? 'selected' : '' }}>Hoy</option>
                        <option value="ayer"   {{ $periodo == 'ayer'   ? 'selected' : '' }}>Ayer</option>
                        <option value="semana" {{ $periodo == 'semana' ? 'selected' : '' }}>Esta Semana</option>
                        <option value="mes"    {{ $periodo == 'mes'    ? 'selected' : '' }}>Este Mes</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="periodo_compras" class="form-label">Compras por periodo</label>
                    <select name="periodo_compras" id="periodo_compras" class="form-select periodo-select">
                        <option value="hoy"    {{ $periodo_compras == 'hoy'    ? 'selected' : '' }}>Hoy</option>
                        <option value="ayer"   {{ $periodo_compras == 'ayer'   ? 'selected' : '' }}>Ayer</option>
                        <option value="semana" {{ $periodo_compras == 'semana' ? 'selected' : '' }}>Esta Semana</option>
                        <option value="mes"    {{ $periodo_compras == 'mes'    ? 'selected' : '' }}>Este Mes</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="fecha" class="form-label">Fecha específica</label>
                    <input type="date" name="fecha" id="fecha" class="form-control"
                           value="{{ request('fecha') }}"
                           max="{{ date('Y-m-d') }}">
                    <small class="text-muted">Muestra stock al final del día</small>
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                </div>

                @if(request('categoria_id') || request('fecha') || request('periodo') || request('periodo_compras'))
                <div class="col-md-1">
                    <a href="{{ route('inventario.index') }}" class="btn btn-secondary w-100" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
                @endif

                <div class="col-md-3 mt-2 mt-md-0">
                    <label for="searchInput" class="form-label">Buscar producto</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar...">
                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center gap-2 flex-wrap">
            <i class="fas fa-table me-1"></i>
            Tabla inventario
            <span class="badge bg-success ms-1">
                Ventas: {{ $periodo == 'hoy' ? 'Hoy' : ($periodo == 'ayer' ? 'Ayer' : ($periodo == 'semana' ? 'Esta Semana' : 'Este Mes')) }}
            </span>
            <span class="badge bg-primary ms-1">
                Compras: {{ $periodo_compras == 'hoy' ? 'Hoy' : ($periodo_compras == 'ayer' ? 'Ayer' : ($periodo_compras == 'semana' ? 'Esta Semana' : 'Este Mes')) }}
            </span>
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table-striped fs-6">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Stock</th>
                        <th>Vendidos</th>
                        <th>Comprados</th>
                        <th>Fecha de Vencimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $item)
                    <tr>
                        <td>{{ $item->codigo }}</td>
                        <td>{{ $item->nombre }}{{ $item->presentacione ? ' - Presentación: ' . $item->presentacione->sigla : '' }}</td>
                        <td>{{ $item->inventario->cantidad ?? 0 }}</td>

                        {{-- Vendidos --}}
                        <td class="vendidos-cell">
                            @if($item->vendidos_periodo > 0)
                                <span class="badge bg-success badge-ventas">{{ $item->vendidos_periodo }}</span>
                            @else
                                <span class="badge bg-secondary badge-ventas">0</span>
                            @endif
                            <button class="btn btn-outline-success btn-sm btn-detalle ms-1"
                                    onclick="verDetalleVentas('{{ $item->id }}', '{{ $periodo }}')"
                                    title="Ver detalle de ventas">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>

                        {{-- Comprados --}}
                        <td class="comprados-cell">
                            @if($item->comprados_periodo > 0)
                                <span class="badge bg-primary badge-compras">{{ $item->comprados_periodo }}</span>
                            @else
                                <span class="badge bg-secondary badge-compras">0</span>
                            @endif
                            <button class="btn btn-outline-primary btn-sm btn-detalle ms-1"
                                    onclick="verDetalleCompras('{{ $item->id }}', '{{ $periodo_compras }}')"
                                    title="Ver detalle de compras">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>

                        <td>{{ $item->inventario?->fecha_vencimiento_format ?? 'N/A' }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                @if($item->inventario && $item->inventario->id)
                                    <a href="{{ route('inventario.edit', $item->inventario->id) }}" class="btn btn-warning">Editar</a>
                                    <form action="{{ route('inventario.destroy', $item->inventario->id) }}" method="POST"
                                        onsubmit="return confirm('¿Estás seguro de que deseas eliminar este elemento?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                @elseif($item->inventario)
                                    <span class="badge bg-secondary">Vista histórica</span>
                                @else
                                    <span class="badge bg-secondary">Sin Inventario</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detalle de Ventas -->
<div class="modal fade" id="ventasDetalleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-chart-bar me-2"></i>Detalle de Ventas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="ventasDetalleLoading" class="text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2 text-muted">Cargando detalles...</p>
                </div>
                <div id="ventasDetalleContent" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0" id="detalleVentasNombre"></h6>
                            <small class="text-muted" id="detalleVentasPeriodo"></small>
                        </div>
                        <span class="badge bg-success fs-6" id="detalleTotalVendidos"></span>
                    </div>
                    <div class="mb-3">
                        <div class="btn-group btn-group-sm" id="modalVentasPeriodoBtns">
                            <button type="button" class="btn btn-outline-success" data-periodo="hoy">Hoy</button>
                            <button type="button" class="btn btn-outline-success" data-periodo="ayer">Ayer</button>
                            <button type="button" class="btn btn-outline-success" data-periodo="semana">Esta Semana</button>
                            <button type="button" class="btn btn-outline-success" data-periodo="mes">Este Mes</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th><th>Hora</th><th>Cliente</th><th>Vendedor</th>
                                    <th class="text-center">Cant.</th><th class="text-end">P. Unit.</th>
                                    <th class="text-end">Total</th><th>Comprobante</th>
                                </tr>
                            </thead>
                            <tbody id="ventasDetalleTabla"></tbody>
                        </table>
                    </div>
                    <div id="ventasDetalleSinDatos" class="text-center py-3" style="display:none;">
                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No hay ventas registradas en este periodo.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle de Compras -->
<div class="modal fade" id="comprasDetalleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-boxes me-2"></i>Detalle de Compras</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="comprasDetalleLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Cargando detalles...</p>
                </div>
                <div id="comprasDetalleContent" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0" id="detalleComprasNombre"></h6>
                            <small class="text-muted" id="detalleComprasPeriodo"></small>
                        </div>
                        <span class="badge bg-primary fs-6" id="detalleTotalComprados"></span>
                    </div>
                    <div class="mb-3">
                        <div class="btn-group btn-group-sm" id="modalComprasPeriodoBtns">
                            <button type="button" class="btn btn-outline-primary" data-periodo="hoy">Hoy</button>
                            <button type="button" class="btn btn-outline-primary" data-periodo="ayer">Ayer</button>
                            <button type="button" class="btn btn-outline-primary" data-periodo="semana">Esta Semana</button>
                            <button type="button" class="btn btn-outline-primary" data-periodo="mes">Este Mes</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th><th>Hora</th><th>Proveedor</th>
                                    <th class="text-center">Cant.</th><th class="text-end">P. Unit.</th>
                                    <th class="text-end">Total</th><th>Comprobante</th>
                                </tr>
                            </thead>
                            <tbody id="comprasDetalleTabla"></tbody>
                        </table>
                    </div>
                    <div id="comprasDetalleSinDatos" class="text-center py-3" style="display:none;">
                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No hay compras registradas en este periodo.</p>
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
<script src="{{ asset('js/simple-datatables.min.js') }}" type="text/javascript"></script>
<script>
    let currentVentasProductoId = null;
    let currentComprasProductoId = null;
    let ventasModal = null;
    let comprasModal = null;

    window.addEventListener('DOMContentLoaded', function() {
        ventasModal  = new bootstrap.Modal(document.getElementById('ventasDetalleModal'));
        comprasModal = new bootstrap.Modal(document.getElementById('comprasDetalleModal'));

        const datatablesSimple = document.getElementById('datatablesSimple');
        if (datatablesSimple) {
            const dataTable = new simpleDatatables.DataTable(datatablesSimple, {
                paging: false,
                searchable: true,
                labels: {
                    placeholder: "Buscar...",
                    perPage: "Registros por página:",
                    noRows: "No se encontraron registros",
                    info: "Mostrando {start} a {end} de {rows} registros",
                    noResults: "No se encontraron resultados para tu búsqueda",
                }
            });
            setTimeout(() => {
                const searchWrapper = datatablesSimple.closest('.datatable-wrapper');
                if (searchWrapper) {
                    const defaultSearch = searchWrapper.querySelector('.datatable-search');
                    if (defaultSearch) defaultSearch.style.display = 'none';
                }
            }, 100);
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function(e) {
                    dataTable.search(e.target.value);
                });
            }
        }

        // Ventas modal period buttons
        document.querySelectorAll('#modalVentasPeriodoBtns button').forEach(btn => {
            btn.addEventListener('click', function() {
                if (currentVentasProductoId) verDetalleVentas(currentVentasProductoId, this.dataset.periodo);
            });
        });

        // Compras modal period buttons
        document.querySelectorAll('#modalComprasPeriodoBtns button').forEach(btn => {
            btn.addEventListener('click', function() {
                if (currentComprasProductoId) verDetalleCompras(currentComprasProductoId, this.dataset.periodo);
            });
        });
    });

    function verDetalleVentas(productoId, periodo) {
        currentVentasProductoId = productoId;
        document.getElementById('ventasDetalleLoading').style.display = 'block';
        document.getElementById('ventasDetalleContent').style.display = 'none';

        document.querySelectorAll('#modalVentasPeriodoBtns button').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.periodo === periodo);
            btn.classList.toggle('btn-success', btn.dataset.periodo === periodo);
            btn.classList.toggle('btn-outline-success', btn.dataset.periodo !== periodo);
        });

        ventasModal.show();

        fetch(`{{ url('admin/inventario/ventas-detalle') }}/${productoId}?periodo=${periodo}`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('ventasDetalleLoading').style.display = 'none';
                document.getElementById('ventasDetalleContent').style.display = 'block';
                document.getElementById('detalleVentasNombre').textContent  = data.producto;
                document.getElementById('detalleVentasPeriodo').textContent  = 'Periodo: ' + data.periodo;
                document.getElementById('detalleTotalVendidos').textContent  = data.total_vendidos + ' unidades vendidas';

                const tabla    = document.getElementById('ventasDetalleTabla');
                const sinDatos = document.getElementById('ventasDetalleSinDatos');
                tabla.innerHTML = '';

                if (data.ventas.length === 0) {
                    sinDatos.style.display = 'block';
                    tabla.closest('.table-responsive').style.display = 'none';
                } else {
                    sinDatos.style.display = 'none';
                    tabla.closest('.table-responsive').style.display = 'block';
                    data.ventas.forEach(v => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${v.fecha}</td><td>${v.hora}</td><td>${v.cliente}</td><td>${v.vendedor}</td>
                            <td class="text-center"><span class="badge bg-success">${v.cantidad}</span></td>
                            <td class="text-end">$${v.precio_unitario}</td>
                            <td class="text-end fw-bold">$${v.total}</td>
                            <td><code>${v.comprobante}</code></td>`;
                        tabla.appendChild(tr);
                    });
                }
            })
            .catch(() => {
                document.getElementById('ventasDetalleLoading').innerHTML =
                    '<div class="text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Error al cargar. Intente de nuevo.</p></div>';
            });
    }

    function verDetalleCompras(productoId, periodo) {
        currentComprasProductoId = productoId;
        document.getElementById('comprasDetalleLoading').style.display = 'block';
        document.getElementById('comprasDetalleContent').style.display = 'none';

        document.querySelectorAll('#modalComprasPeriodoBtns button').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.periodo === periodo);
            btn.classList.toggle('btn-primary', btn.dataset.periodo === periodo);
            btn.classList.toggle('btn-outline-primary', btn.dataset.periodo !== periodo);
        });

        comprasModal.show();

        fetch(`{{ url('admin/inventario/compras-detalle') }}/${productoId}?periodo=${periodo}`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('comprasDetalleLoading').style.display = 'none';
                document.getElementById('comprasDetalleContent').style.display = 'block';
                document.getElementById('detalleComprasNombre').textContent   = data.producto;
                document.getElementById('detalleComprasPeriodo').textContent  = 'Periodo: ' + data.periodo;
                document.getElementById('detalleTotalComprados').textContent  = data.total_comprados + ' unidades compradas';

                const tabla    = document.getElementById('comprasDetalleTabla');
                const sinDatos = document.getElementById('comprasDetalleSinDatos');
                tabla.innerHTML = '';

                if (data.compras.length === 0) {
                    sinDatos.style.display = 'block';
                    tabla.closest('.table-responsive').style.display = 'none';
                } else {
                    sinDatos.style.display = 'none';
                    tabla.closest('.table-responsive').style.display = 'block';
                    data.compras.forEach(c => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${c.fecha}</td><td>${c.hora}</td><td>${c.proveedor}</td>
                            <td class="text-center"><span class="badge bg-primary">${c.cantidad}</span></td>
                            <td class="text-end">$${c.precio_unitario}</td>
                            <td class="text-end fw-bold">$${c.total}</td>
                            <td><code>${c.comprobante}</code></td>`;
                        tabla.appendChild(tr);
                    });
                }
            })
            .catch(() => {
                document.getElementById('comprasDetalleLoading').innerHTML =
                    '<div class="text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Error al cargar. Intente de nuevo.</p></div>';
            });
    }
</script>
@endpush
