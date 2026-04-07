@extends('layouts.app')

@section('title', 'Estadísticas')

@push('css')
<style>
    .kpi-value { font-family: 'JetBrains Mono', monospace; }
    .chart-wrap { height: 280px; }
</style>
@endpush

@section('content')
<div class="container-fluid px-2 pt-3">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0" style="font-weight:800;color:var(--text-primary);">
                <i class="fas fa-chart-pie me-2" style="color:var(--color-primary);"></i>
                Estadísticas
            </h4>
            <div style="font-size:0.82rem;color:var(--text-secondary);">
                Análisis del negocio por periodo
            </div>
        </div>
    </div>

    <div class="filter-card mb-4">
        <form action="{{ route('admin.estadisticas') }}" method="GET" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                    <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" value="{{ $fechaInicio }}">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label for="fecha_fin" class="form-label">Fecha fin</label>
                    <input type="date" class="form-control" name="fecha_fin" id="fecha_fin" value="{{ $fechaFin }}">
                </div>
                <div class="col-md-6">
                    @php $preset = request('preset', 'custom'); @endphp
                    <input type="hidden" name="preset" id="preset_input" value="{{ $preset }}">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="preset-btn {{ $preset === 'today' ? 'active' : '' }}" onclick="setDatePreset('today')">Hoy</button>
                        <button type="button" class="preset-btn {{ $preset === 'yesterday' ? 'active' : '' }}" onclick="setDatePreset('yesterday')">Ayer</button>
                        <button type="button" class="preset-btn {{ $preset === 'week' ? 'active' : '' }}" onclick="setDatePreset('week')">Esta semana</button>
                        <button type="button" class="preset-btn {{ $preset === 'month' ? 'active' : '' }}" onclick="setDatePreset('month')">Este mes</button>
                        <button type="button" class="preset-btn {{ $preset === 'year' ? 'active' : '' }}" onclick="setDatePreset('year')">Este año</button>
                        <button type="submit" class="btn btn-primary ms-auto" style="height:34px;font-size:0.82rem;">
                            <i class="fas fa-filter me-1"></i>Filtrar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card success h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Total del período</div>
                        <div class="kpi-value" style="font-size:1.35rem;">${{ number_format($ventasPeriodo, 0, ',', '.') }}</div>
                        <div class="small" style="color:var(--text-secondary);font-size:0.72rem;">
                            {{ $fechaInicio == $fechaFin ? 'Hoy' : $fechaInicio . ' - ' . $fechaFin }}
                        </div>
                    </div>
                    <div class="kpi-icon success ms-2"><i class="fas fa-cash-register"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="kpi-card primary h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Efectivo</div>
                        <div class="kpi-value" style="font-size:1.35rem;">${{ number_format($ventasEfectivo, 0, ',', '.') }}</div>
                        <div class="small text-muted">Cobros en efectivo</div>
                    </div>
                    <div class="kpi-icon primary ms-2"><i class="fas fa-money-bill-wave"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="kpi-card info h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Nequi</div>
                        <div class="kpi-value" style="font-size:1.35rem;">${{ number_format($ventasNequi, 0, ',', '.') }}</div>
                        <div class="small" style="color:var(--color-info);">Transferencias Nequi</div>
                    </div>
                    <div class="kpi-icon info ms-2"><i class="fas fa-mobile-alt"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="kpi-card warning h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Daviplata</div>
                        <div class="kpi-value" style="font-size:1.35rem;">${{ number_format($ventasDaviplata, 0, ',', '.') }}</div>
                        <div class="small" style="color:var(--color-warning);">Transferencias Daviplata</div>
                    </div>
                    <div class="kpi-icon warning ms-2"><i class="fas fa-university"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="kpi-card info h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Clientes</div>
                        <div class="kpi-value">{{ number_format($totalClientes, 0, ',', '.') }}</div>
                        <div class="small" style="color:var(--color-info);">Clientes registrados</div>
                    </div>
                    <div class="kpi-icon info ms-2"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card warning h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Productos</div>
                        <div class="kpi-value">{{ number_format($totalProductos, 0, ',', '.') }}</div>
                        <div class="small" style="color:var(--color-warning);">Productos activos</div>
                    </div>
                    <div class="kpi-icon warning ms-2"><i class="fas fa-box-open"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card primary h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Compras</div>
                        <div class="kpi-value">{{ number_format($totalCompras, 0, ',', '.') }}</div>
                        <div class="small text-muted">Compras registradas</div>
                    </div>
                    <div class="kpi-icon primary ms-2"><i class="fas fa-shopping-cart"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card success h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Usuarios</div>
                        <div class="kpi-value">{{ number_format($totalUsuarios, 0, ',', '.') }}</div>
                        <div class="small" style="color:var(--color-success);">Usuarios del sistema</div>
                    </div>
                    <div class="kpi-icon success ms-2"><i class="fas fa-user-gear"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="chart-card h-100">
                <div class="chart-card-header d-flex align-items-center justify-content-between">
                    <h6><i class="fas fa-chart-area me-2" style="color:var(--color-primary);"></i>Ventas por día</h6>
                    <div style="font-size:0.75rem;color:var(--text-secondary);">Período seleccionado</div>
                </div>
                <div class="chart-card-body">
                    <div class="chart-wrap"><canvas id="ventasChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="chart-card-header">
                    <h6><i class="fas fa-exclamation-triangle me-2" style="color:var(--color-danger);"></i>Stock bajo</h6>
                </div>
                <div class="chart-card-body">
                    <div class="chart-wrap"><canvas id="productosStockBajoChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="chart-card h-100">
                <div class="chart-card-header d-flex align-items-center justify-content-between">
                    <h6><i class="fas fa-boxes me-2" style="color:var(--color-info);"></i>Inventario</h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary active" onclick="updateStockChart('masStock', this)">Más stock</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateStockChart('menosStock', this)">Menos stock</button>
                    </div>
                </div>
                <div class="chart-card-body">
                    <div style="height:240px;"><canvas id="dynamicStockChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-card h-100">
                <div class="chart-card-header d-flex align-items-center justify-content-between">
                    <h6><i class="fas fa-fire me-2" style="color:var(--color-warning);"></i>Productos vendidos</h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary active" onclick="updateSalesChart('masVendidos', this)">Más vendidos</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateSalesChart('menosVendidos', this)">Menos vendidos</button>
                    </div>
                </div>
                <div class="chart-card-body">
                    <div style="height:240px;"><canvas id="dynamicSalesChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <i class="fas fa-users me-2" style="color:var(--color-primary);"></i>
                        <span>Ventas por cliente</span>
                    </div>
                    <span class="badge" style="background:var(--color-primary-subtle);color:var(--color-primary);">
                        {{ collect($ventasPorCliente)->count() }} clientes
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Transacciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventasPorCliente as $clienteId => $ventas)
                                <tr>
                                    <td style="font-weight:500;">
                                        {{ $ventas->first()->cliente?->persona?->razon_social ?? 'Cliente general' }}
                                    </td>
                                    <td>
                                        <span class="text-cop fw-bold" style="color:var(--color-success);">
                                            ${{ number_format($ventas->sum('total'), 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background:var(--color-info-subtle);color:var(--color-info);">
                                            {{ $ventas->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-sm"
                                            style="background:var(--color-info-subtle);color:var(--color-info);border:none;height:30px;"
                                            onclick="showTransacciones('{{ $clienteId ?: 'general' }}', '{{ addslashes($ventas->first()->cliente?->persona?->razon_social ?? 'Cliente general') }}')">
                                            <i class="fas fa-eye me-1"></i>Ver
                                        </button>
                                        <div id="data_transacciones_{{ $clienteId ?: 'general' }}" class="d-none">
                                            @foreach($ventas as $venta)
                                            <div class="tx-item"
                                                data-fecha="{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y H:i') }}"
                                                data-total="{{ number_format($venta->total, 0, ',', '.') }}"
                                                data-vendedor="{{ $venta->user?->name ?? 'N/A' }}"
                                                data-metodo="{{ $venta->metodo_pago }}"></div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4" style="color:var(--text-muted);">
                                        No hay ventas en este período.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-exclamation-triangle me-2" style="color:var(--color-danger);"></i>
                    <span style="color:var(--color-danger);font-weight:700;">Stock bajo</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productosStockBajo as $producto)
                                <tr>
                                    <td style="font-size:0.85rem;font-weight:500;">{{ $producto->nombre }}</td>
                                    <td>
                                        <span class="badge" style="background:var(--color-danger-subtle);color:var(--color-danger);">
                                            {{ $producto->cantidad }} uds
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center py-3" style="color:var(--text-muted);font-size:0.85rem;">
                                        <i class="fas fa-check-circle me-1" style="color:var(--color-success);"></i>
                                        Sin alertas de stock
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="transaccionesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--color-primary);">
                <h5 class="modal-title" style="color:#fff;font-size:0.95rem;font-weight:700;">
                    <i class="fas fa-list me-2"></i>Transacciones - <span id="modalClientName"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha y hora</th>
                                <th>Total</th>
                                <th>Método</th>
                                <th>Vendedor</th>
                            </tr>
                        </thead>
                        <tbody id="transaccionesModalBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('js/Chart.min.js') }}"></script>
<script>
function showTransacciones(clienteId, clienteNombre) {
    document.getElementById('modalClientName').textContent = clienteNombre;
    var tbody = document.getElementById('transaccionesModalBody');
    tbody.innerHTML = '';
    var items = document.getElementById('data_transacciones_' + clienteId).querySelectorAll('.tx-item');
    var colors = { efectivo: 'success', nequi: 'info', daviplata: 'warning', tarjeta: 'primary' };

    items.forEach(function(item) {
        var metodo = (item.getAttribute('data-metodo') || '').toLowerCase();
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td style="font-size:0.82rem;">' + item.getAttribute('data-fecha') + '</td>' +
            '<td class="fw-bold text-cop" style="color:var(--color-success);">$' + item.getAttribute('data-total') + '</td>' +
            '<td><span class="badge bg-' + (colors[metodo] || 'secondary') + '">' + item.getAttribute('data-metodo') + '</span></td>' +
            '<td style="font-size:0.82rem;color:var(--text-secondary);">' + item.getAttribute('data-vendedor') + '</td>';
        tbody.appendChild(tr);
    });

    new bootstrap.Modal(document.getElementById('transaccionesModal')).show();
}

function getThemeColors() {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';

    return {
        textColor: isDark ? '#9CA3AF' : '#6b7280',
        gridColor: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
        primary: '#C8553D',
        accent: '#F0C75E',
        success: '#4CAF7D',
        info: '#5B9BD5',
        warning: '#F5A623',
        danger: '#E74C5E',
    };
}

function setDatePreset(preset) {
    var now = new Date();
    var start = new Date(now);
    var end = new Date(now);

    if (preset === 'yesterday') {
        start.setDate(now.getDate() - 1);
        end.setDate(now.getDate() - 1);
    } else if (preset === 'week') {
        var day = now.getDay();
        var diff = day === 0 ? 6 : day - 1;
        start.setDate(now.getDate() - diff);
    } else if (preset === 'month') {
        start = new Date(now.getFullYear(), now.getMonth(), 1);
    } else if (preset === 'year') {
        start = new Date(now.getFullYear(), 0, 1);
    }

    document.getElementById('preset_input').value = preset;
    document.getElementById('fecha_inicio').value = formatDateInput(start);
    document.getElementById('fecha_fin').value = formatDateInput(end);
    document.getElementById('filterForm').submit();
}

function formatDateInput(date) {
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var day = String(date.getDate()).padStart(2, '0');
    return date.getFullYear() + '-' + month + '-' + day;
}

var tc = getThemeColors();
var datosVenta = @json($totalVentasPorDia);
var fechas = datosVenta.map(function(item) {
    return item.fecha ? item.fecha.split('-').reverse().join('/') : 'Sin fecha';
});
var montos = datosVenta.map(function(item) {
    return parseFloat(item.total);
});

new Chart(document.getElementById('ventasChart'), {
    type: 'line',
    data: {
        labels: fechas,
        datasets: [{
            label: 'Ventas',
            data: montos,
            lineTension: 0.3,
            backgroundColor: 'rgba(200,85,61,0.07)',
            borderColor: tc.primary,
            borderWidth: 2.5,
            pointRadius: 4,
            pointBackgroundColor: tc.primary,
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }]
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            xAxes: [{ gridLines: { display: false, drawBorder: false }, ticks: { fontColor: tc.textColor } }],
            yAxes: [{
                ticks: {
                    fontColor: tc.textColor,
                    callback: function(value) { return '$' + value.toLocaleString('es-CO'); }
                },
                gridLines: { color: tc.gridColor, drawBorder: false }
            }]
        },
        legend: { display: false }
    }
});

var stockBajo = @json($productosStockBajo);
new Chart(document.getElementById('productosStockBajoChart'), {
    type: 'doughnut',
    data: {
        labels: stockBajo.map(function(item) { return item.nombre; }),
        datasets: [{
            data: stockBajo.map(function(item) { return item.cantidad; }),
            backgroundColor: ['#E74C5E', '#F5A623', '#F0C75E', '#5B9BD5', '#4CAF7D'],
        }]
    },
    options: {
        maintainAspectRatio: false,
        legend: {
            position: 'bottom',
            labels: { fontColor: tc.textColor }
        }
    }
});

var dataMasStock = @json($productosMasStock);
var dataMenosStock = @json($productosStockBajo);
var stockChartInst = null;

function renderStockChart(data) {
    var ctx = document.getElementById('dynamicStockChart');
    if (stockChartInst) stockChartInst.destroy();

    stockChartInst = new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: data.map(function(item) { return item.nombre; }),
            datasets: [{
                data: data.map(function(item) { return item.cantidad; }),
                backgroundColor: tc.info + 'CC',
                borderColor: tc.info,
                borderWidth: 1.5
            }]
        },
        options: {
            maintainAspectRatio: false,
            legend: { display: false },
            scales: {
                xAxes: [{ ticks: { beginAtZero: true, fontColor: tc.textColor }, gridLines: { display: false, drawBorder: false } }],
                yAxes: [{ ticks: { fontColor: tc.textColor }, gridLines: { color: tc.gridColor, drawBorder: false } }]
            }
        }
    });
}

window.updateStockChart = function(type, btn) {
    btn.parentElement.querySelectorAll('.btn').forEach(function(button) {
        button.classList.remove('active');
    });
    btn.classList.add('active');
    renderStockChart(type === 'masStock' ? dataMasStock : dataMenosStock);
};

var dataMasVendidos = @json($productosMasVendidos);
var dataMenosVendidos = @json($productosMenosVendidos);
var salesChartInst = null;

function renderSalesChart(data) {
    var ctx = document.getElementById('dynamicSalesChart');
    if (salesChartInst) salesChartInst.destroy();

    salesChartInst = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(function(item) { return item.nombre; }),
            datasets: [{
                data: data.map(function(item) { return item.total_vendido; }),
                backgroundColor: tc.warning + 'CC',
                borderColor: tc.warning,
                borderWidth: 1.5
            }]
        },
        options: {
            maintainAspectRatio: false,
            legend: { display: false },
            scales: {
                xAxes: [{ ticks: { fontColor: tc.textColor }, gridLines: { display: false, drawBorder: false } }],
                yAxes: [{ ticks: { beginAtZero: true, fontColor: tc.textColor }, gridLines: { color: tc.gridColor, drawBorder: false } }]
            }
        }
    });
}

window.updateSalesChart = function(type, btn) {
    btn.parentElement.querySelectorAll('.btn').forEach(function(button) {
        button.classList.remove('active');
    });
    btn.classList.add('active');
    renderSalesChart(type === 'masVendidos' ? dataMasVendidos : dataMenosVendidos);
};

renderStockChart(dataMasStock);
renderSalesChart(dataMasVendidos);
</script>
@endpush
