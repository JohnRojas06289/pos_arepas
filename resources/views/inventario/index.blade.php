@extends('layouts.app')

@section('title','Inventario')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<style>
    .badge-ventas {
        font-size: 0.95em;
        min-width: 32px;
        display: inline-block;
    }
    .btn-detalle {
        padding: 2px 8px;
        font-size: 0.78em;
    }
    #ventasDetalleModal .table th {
        font-size: 0.85em;
        white-space: nowrap;
    }
    #ventasDetalleModal .table td {
        font-size: 0.85em;
    }
    .periodo-select {
        max-width: 180px;
    }
    .vendidos-cell {
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
            <div class="row align-items-end">
                <div class="col-md-4">
                    <form action="{{ route('inventario.index') }}" method="GET">
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
                            <option value="hoy" {{ $periodo == 'hoy' ? 'selected' : '' }}>Hoy</option>
                            <option value="ayer" {{ $periodo == 'ayer' ? 'selected' : '' }}>Ayer</option>
                            <option value="semana" {{ $periodo == 'semana' ? 'selected' : '' }}>Esta Semana</option>
                            <option value="mes" {{ $periodo == 'mes' ? 'selected' : '' }}>Este Mes</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="fecha" class="form-label">Fecha específica</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" 
                               value="{{ request('fecha') }}" 
                               max="{{ date('Y-m-d') }}">
                        <small class="text-muted">Muestra stock al final del día</small>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                     </div>
                    </form>
                    
                    @if(request('categoria_id') || request('fecha') || request('periodo'))
                    <div class="col-md-1">
                        <a href="{{ route('inventario.index') }}" class="btn btn-secondary w-100" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                    @endif
                    
                    <div class="col-md-3 mt-3 mt-md-0">
                        <label for="searchInput" class="form-label">Buscar producto</label>
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla inventario
            <span class="badge bg-info ms-2">
                Periodo: {{ $periodo == 'hoy' ? 'Hoy' : ($periodo == 'ayer' ? 'Ayer' : ($periodo == 'semana' ? 'Esta Semana' : 'Este Mes')) }}
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
                        <th>Fecha de Vencimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $item)
                    <tr>
                        <td>
                            {{$item->codigo}}
                        </td>
                        <td>
                             {{$item->nombre}}{{ $item->presentacione ? ' - Presentación: ' . $item->presentacione->sigla : '' }}
                        </td>
                        <td>
                            {{$item->inventario->cantidad ?? 0}}
                        </td>
                        <td class="vendidos-cell">
                            @if($item->vendidos_periodo > 0)
                                <span class="badge bg-success badge-ventas">{{ $item->vendidos_periodo }}</span>
                            @else
                                <span class="badge bg-secondary badge-ventas">0</span>
                            @endif
                            <button class="btn btn-outline-primary btn-sm btn-detalle ms-1" 
                                    onclick="verDetalleVentas('{{ $item->id }}', '{{ $periodo }}')"
                                    title="Ver detalle de ventas">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                        <td>
                            {{$item->inventario?->fecha_vencimiento_format ?? 'N/A'}}
                        </td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                @if($item->inventario)
                                    <a href="{{ route('inventario.edit', $item->inventario->id) }}" class="btn btn-warning">Editar</a>
                                    <form action="{{ route('inventario.destroy', $item->inventario->id) }}" method="POST"
                                        onsubmit="return confirm('¿Estás seguro de que deseas eliminar este elemento?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
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

<!-- Modal de Detalle de Ventas -->
<div class="modal fade" id="ventasDetalleModal" tabindex="-1" aria-labelledby="ventasDetalleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="ventasDetalleModalLabel">
                    <i class="fas fa-chart-bar me-2"></i>Detalle de Ventas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="ventasDetalleLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando detalles...</p>
                </div>
                <div id="ventasDetalleContent" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0" id="detalleProductoNombre"></h6>
                            <small class="text-muted" id="detallePeriodo"></small>
                        </div>
                        <div>
                            <span class="badge bg-success fs-6" id="detalleTotalVendidos"></span>
                        </div>
                    </div>

                    <!-- Selector de periodo dentro del modal -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cambiar periodo:</label>
                        <div class="btn-group btn-group-sm" role="group" id="modalPeriodoBtns">
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
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio Unit.</th>
                                    <th class="text-end">Total</th>
                                    <th>Comprobante</th>
                                </tr>
                            </thead>
                            <tbody id="ventasDetalleTabla">
                            </tbody>
                        </table>
                    </div>
                    <div id="ventasDetalleSinDatos" class="text-center py-3" style="display: none;">
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

@endsection

@push('js')
<script src="{{ asset('js/simple-datatables.min.js') }}" type="text/javascript"></script>
<script>
    let currentProductoId = null;
    let ventasModal = null; // Single modal instance, reused

    window.addEventListener('DOMContentLoaded', event => {
        // Create modal instance once
        ventasModal = new bootstrap.Modal(document.getElementById('ventasDetalleModal'), {
            backdrop: true,
            keyboard: true
        });

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

            // Hide default search input container
            setTimeout(() => {
                const searchWrapper = datatablesSimple.closest('.datatable-wrapper');
                if(searchWrapper) {
                     const defaultSearch = searchWrapper.querySelector('.datatable-search');
                     if(defaultSearch) defaultSearch.style.display = 'none';
                }
            }, 100);

            // Custom search input
            const searchInput = document.getElementById('searchInput');
            if(searchInput){
                searchInput.addEventListener('keyup', function(e) {
                    dataTable.search(e.target.value);
                });
            }
        }

        // Modal period buttons
        document.querySelectorAll('#modalPeriodoBtns button').forEach(btn => {
            btn.addEventListener('click', function() {
                if (currentProductoId) {
                    verDetalleVentas(currentProductoId, this.dataset.periodo);
                }
            });
        });
    });

    function verDetalleVentas(productoId, periodo) {
        currentProductoId = productoId;
        
        // Show loading, hide content
        document.getElementById('ventasDetalleLoading').style.display = 'block';
        document.getElementById('ventasDetalleLoading').innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando detalles...</p>
        `;
        document.getElementById('ventasDetalleContent').style.display = 'none';
        
        // Highlight active period button
        document.querySelectorAll('#modalPeriodoBtns button').forEach(btn => {
            btn.classList.remove('active', 'btn-primary');
            btn.classList.add('btn-outline-primary');
            if (btn.dataset.periodo === periodo) {
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('active', 'btn-primary');
            }
        });

        // Reuse single modal instance (prevents backdrop stacking)
        ventasModal.show();

        fetch(`{{ url('admin/inventario/ventas-detalle') }}/${productoId}?periodo=${periodo}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('ventasDetalleLoading').style.display = 'none';
                document.getElementById('ventasDetalleContent').style.display = 'block';

                document.getElementById('detalleProductoNombre').textContent = data.producto;
                document.getElementById('detallePeriodo').textContent = 'Periodo: ' + data.periodo;
                document.getElementById('detalleTotalVendidos').textContent = data.total_vendidos + ' unidades vendidas';

                const tabla = document.getElementById('ventasDetalleTabla');
                const sinDatos = document.getElementById('ventasDetalleSinDatos');
                
                tabla.innerHTML = '';

                if (data.ventas.length === 0) {
                    sinDatos.style.display = 'block';
                    tabla.closest('.table-responsive').style.display = 'none';
                } else {
                    sinDatos.style.display = 'none';
                    tabla.closest('.table-responsive').style.display = 'block';
                    
                    data.ventas.forEach(venta => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${venta.fecha}</td>
                            <td>${venta.hora}</td>
                            <td>${venta.cliente}</td>
                            <td>${venta.vendedor}</td>
                            <td class="text-center"><span class="badge bg-primary">${venta.cantidad}</span></td>
                            <td class="text-end">$${venta.precio_unitario}</td>
                            <td class="text-end fw-bold">$${venta.total}</td>
                            <td><code>${venta.comprobante}</code></td>
                        `;
                        tabla.appendChild(row);
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('ventasDetalleLoading').innerHTML = `
                    <div class="text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p>Error al cargar los detalles. Intente de nuevo.</p>
                    </div>
                `;
            });
    }
</script>
@endpush
