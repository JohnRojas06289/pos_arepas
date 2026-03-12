@extends('layouts.app')

@section('title', 'Estadísticas')

@push('css')
<style>
    .kpi-value { font-family: 'JetBrains Mono', monospace; }
</style>
@endpush

@section('content')
<div class="container-fluid px-2 pt-3">

    {{-- Encabezado --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0" style="font-weight:800;color:var(--text-primary);">
                <i class="fas fa-chart-pie me-2" style="color:var(--color-primary);"></i>
                Estadísticas
            </h4>
            <div style="font-size:0.82rem;color:var(--text-secondary);">
                Análisis para toma de decisiones
            </div>
        </div>
    </div>

    {{-- Filtros de Fecha --}}
    <div class="filter-card">
        <form action="{{ route('admin.estadisticas') }}" method="GET" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                    <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio"
                           value="{{ $fechaInicio }}">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label for="fecha_fin" class="form-label">Fecha fin</label>
                    <input type="date" class="form-control" name="fecha_fin" id="fecha_fin"
                           value="{{ $fechaFin }}">
                </div>
                <div class="col-md-6">
                    @php $preset = request('preset', 'custom'); @endphp
                    <input type="hidden" name="preset" id="preset_input" value="{{ $preset }}">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="preset-btn {{ $preset=='today'     ? 'active' : '' }}" onclick="setDatePreset('today')">Hoy</button>
                        <button type="button" class="preset-btn {{ $preset=='yesterday' ? 'active' : '' }}" onclick="setDatePreset('yesterday')">Ayer</button>
                        <button type="button" class="preset-btn {{ $preset=='week'      ? 'active' : '' }}" onclick="setDatePreset('week')">Esta semana</button>
                        <button type="button" class="preset-btn {{ $preset=='month'     ? 'active' : '' }}" onclick="setDatePreset('month')">Este mes</button>
                        <button type="button" class="preset-btn {{ $preset=='year'      ? 'active' : '' }}" onclick="setDatePreset('year')">Este año</button>
                        <button type="button"
                                onclick="document.getElementById('preset_input').value='custom';document.getElementById('filterForm').submit();"
                                class="btn btn-primary ms-auto" style="height:34px;font-size:0.82rem;">
                            <i class="fas fa-filter me-1"></i>Filtrar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- KPIs del período --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card success h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Total período</div>
                        <div class="kpi-value" style="font-size:1.35rem;">${{ number_format($ventasPeriodo, 0, ',', '.') }}</div>
                        <div class="small" style="color:var(--text-secondary);font-size:0.72rem;">
                            {{ $fechaInicio == $fechaFin ? 'Hoy' : $fechaInicio . ' — ' . $fechaFin }}
                        </div>
                    </div>
                    <div class="kpi-icon success ms-2"><i class="fas fa-cash-register"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card primary h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Efectivo</div>
                        <div class="kpi-value" style="font-size:1.35rem;">${{ number_format($ventasEfectivo, 0, ',', '.') }}</div>
                        <div class="small text-muted">En efectivo</div>
                    </div>
                    <div class="kpi-icon primary ms-2"><i class="fas fa-money-bill-wave"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card info h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Nequi</div>
                        <div class="kpi-value" style="font-size:1.35rem;">${{ number_format($ventasNequi, 0, ',', '.') }}</div>
                        <div class="small" style="color:var(--color-info);">Transferencia</div>
                    </div>
                    <div class="kpi-icon info ms-2"><i class="fas fa-mobile-alt"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card warning h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Daviplata</div>
                        <div class="kpi-value" style="font-size:1.35rem;">${{ number_format($ventasDaviplata, 0, ',', '.') }}</div>
                        <div class="small" style="color:var(--color-warning);">Transferencia</div>
                    </div>
                    <div class="kpi-icon warning ms-2"><i class="fas fa-university"></i></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="kpi-card danger h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Fiado</div>
                        <div class="kpi-value" style="font-size:1.35rem;">${{ number_format($ventasFiado, 0, ',', '.') }}</div>
                        <div class="small" style="color:var(--color-danger);">Pendiente</div>
                    </div>
                    <div class="kpi-icon danger ms-2"><i class="fas fa-handshake"></i></div>
                </div>
            </div>
        </div>

    </div>

    {{-- KPIs secundarios --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="kpi-card info h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Total clientes</div>
                        <div class="kpi-value">{{ number_format($totalClientes, 0, ',', '.') }}</div>
                        <div class="small" style="color:var(--color-info);">Clientes activos</div>
                    </div>
                    <div class="kpi-icon info ms-2"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card warning h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Productos</div>
                        <div class="kpi-value">{{ number_format($totalProductos, 0, ',', '.') }}</div>
                        <div class="small" style="color:var(--color-warning);">En inventario</div>
                    </div>
                    <div class="kpi-icon warning ms-2"><i class="fas fa-box-open"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card primary h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Compras</div>
                        <div class="kpi-value">{{ number_format($totalCompras, 0, ',', '.') }}</div>
                        <div class="small text-muted">Total registradas</div>
                    </div>
                    <div class="kpi-icon primary ms-2"><i class="fas fa-shopping-cart"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficas --}}
    <div class="row g-4 mb-4">

        {{-- Ventas por día --}}
        <div class="col-lg-8">
            <div class="chart-card h-100">
                <div class="chart-card-header d-flex align-items-center justify-content-between">
                    <h6><i class="fas fa-chart-area me-2" style="color:var(--color-primary);"></i>Ventas por día</h6>
                    <div style="font-size:0.75rem;color:var(--text-secondary);">Período seleccionado</div>
                </div>
                <div class="chart-card-body">
                    <div style="height:280px;"><canvas id="ventasChart"></canvas></div>
                </div>
            </div>
        </div>

        {{-- Stock bajo --}}
        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="chart-card-header">
                    <h6><i class="fas fa-exclamation-triangle me-2" style="color:var(--color-danger);"></i>Alerta stock bajo</h6>
                </div>
                <div class="chart-card-body">
                    <div style="height:280px;"><canvas id="productosStockBajoChart"></canvas></div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4 mb-4">

        {{-- Inventario dinámico --}}
        <div class="col-lg-6">
            <div class="chart-card h-100">
                <div class="chart-card-header d-flex align-items-center justify-content-between">
                    <h6><i class="fas fa-boxes me-2" style="color:var(--color-info);"></i>Inventario</h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary active" onclick="updateStockChart('masStock',this)">Más stock</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateStockChart('menosStock',this)">Menos stock</button>
                    </div>
                </div>
                <div class="chart-card-body">
                    <div style="height:240px;"><canvas id="dynamicStockChart"></canvas></div>
                </div>
            </div>
        </div>

        {{-- Ventas por producto --}}
        <div class="col-lg-6">
            <div class="chart-card h-100">
                <div class="chart-card-header d-flex align-items-center justify-content-between">
                    <h6><i class="fas fa-fire me-2" style="color:var(--color-warning);"></i>Productos vendidos</h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary active" onclick="updateSalesChart('masVendidos',this)">Más vendidos</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateSalesChart('menosVendidos',this)">Menos vendidos</button>
                    </div>
                </div>
                <div class="chart-card-body">
                    <div style="height:240px;"><canvas id="dynamicSalesChart"></canvas></div>
                </div>
            </div>
        </div>

    </div>

    {{-- Tablas --}}
    <div class="row g-4">

        {{-- Ventas por cliente --}}
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
                                        {{ $ventas->first()->cliente ? $ventas->first()->cliente->persona->razon_social : 'Cliente General' }}
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
                                            onclick="showTransacciones('{{ $clienteId ? $clienteId : 'general' }}', '{{ $ventas->first()->cliente ? addslashes($ventas->first()->cliente->persona->razon_social) : 'Cliente General' }}')">
                                            <i class="fas fa-eye me-1"></i>Ver
                                        </button>
                                        <div id="data_transacciones_{{ $clienteId ? $clienteId : 'general' }}" class="d-none">
                                            @foreach($ventas as $v)
                                            <div class="tx-item"
                                                 data-fecha="{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i') }}"
                                                 data-total="{{ number_format($v->total, 0, ',', '.') }}"
                                                 data-vendedor="{{ $v->user->name }}"
                                                 data-metodo="{{ $v->metodo_pago }}"></div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4" style="color:var(--text-muted);">
                                        No hay ventas en este periodo
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stock bajo lista --}}
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-exclamation-triangle me-2" style="color:var(--color-danger);"></i>
                    <span style="color:var(--color-danger);font-weight:700;">Stock bajo (&lt;10 uds)</span>
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
                                @foreach($productosStockBajo as $p)
                                <tr>
                                    <td style="font-size:0.85rem;font-weight:500;">{{ $p['nombre'] ?? $p->nombre }}</td>
                                    <td>
                                        <span class="badge" style="background:var(--color-danger-subtle);color:var(--color-danger);">
                                            {{ $p['cantidad'] ?? $p->cantidad }} uds
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                @if(empty($productosStockBajo) || count($productosStockBajo) === 0)
                                <tr>
                                    <td colspan="2" class="text-center py-3" style="color:var(--text-muted);font-size:0.85rem;">
                                        <i class="fas fa-check-circle me-1" style="color:var(--color-success);"></i>
                                        Sin alertas de stock
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- Modal: transacciones del cliente --}}
<div class="modal fade" id="transaccionesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--color-primary);">
                <h5 class="modal-title" style="color:#fff;font-size:0.95rem;font-weight:700;">
                    <i class="fas fa-list me-2"></i>Transacciones — <span id="modalClientName"></span>
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
<script>
function showTransacciones(clienteId, clienteNombre) {
    document.getElementById('modalClientName').textContent = clienteNombre;
    var tbody = document.getElementById('transaccionesModalBody');
    tbody.innerHTML = '';
    var items = document.getElementById('data_transacciones_' + clienteId).querySelectorAll('.tx-item');
    var colors = { efectivo:'success', nequi:'info', daviplata:'warning', fiado:'danger', tarjeta:'primary' };
    items.forEach(function(item) {
        var metodo = (item.getAttribute('data-metodo') || '').toLowerCase();
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td style="font-size:0.82rem;">' + item.getAttribute('data-fecha') + '</td>' +
            '<td class="fw-bold text-cop" style="color:var(--color-success);">$' + item.getAttribute('data-total') + '</td>' +
            '<td><span class="badge bg-' + (colors[metodo]||'secondary') + '">' + item.getAttribute('data-metodo') + '</span></td>' +
            '<td style="font-size:0.82rem;color:var(--text-secondary);">' + item.getAttribute('data-vendedor') + '</td>';
        tbody.appendChild(tr);
    });
    new bootstrap.Modal(document.getElementById('transaccionesModal')).show();
}
</script>
@endpush

@push('js')
<script src="{{ asset('js/Chart.min.js') }}"></script>
<script src="{{ asset('js/simple-datatables.min.js') }}" crossorigin="anonymous"></script>

<script>
// Función para obtener colores del tema actual
function getThemeColors() {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    return {
        textColor:  isDark ? '#9CA3AF' : '#6b7280',
        gridColor:  isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
        bgColor:    isDark ? '#2A2A3C' : '#FFFFFF',
        primary:    '#C8553D',
        accent:     '#F0C75E',
        success:    '#4CAF7D',
        info:       '#5B9BD5',
        warning:    '#F5A623',
        danger:     '#E74C5E',
        palette:    ['#C8553D','#F0C75E','#4CAF7D','#5B9BD5','#F5A623','#2D3A3A']
    };
}

var tc = getThemeColors();
Chart.defaults.global.defaultFontFamily = 'Inter, sans-serif';
Chart.defaults.global.defaultFontColor  = tc.textColor;

// ---- Gráfico de ventas por día ----
var datosVenta = @json($totalVentasPorDia);
var fechas = datosVenta.map(function(v) {
    if (!v.fecha) return 'Sin fecha';
    var s = (typeof v.fecha === 'string' ? v.fecha : v.fecha.toString()).split(' ')[0].split('-');
    return s.length === 3 ? s[2]+'/'+s[1]+'/'+s[0] : v.fecha;
});
var montos = datosVenta.map(function(v) { return parseFloat(v.total); });

new Chart(document.getElementById('ventasChart'), {
    type: 'line',
    data: {
        labels: fechas,
        datasets: [{
            label: 'Ventas',
            lineTension: 0.3,
            backgroundColor: 'rgba(200,85,61,0.07)',
            borderColor:     tc.primary,
            borderWidth: 2.5,
            pointRadius: 4,
            pointBackgroundColor: tc.primary,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointHoverRadius: 6,
            pointHitRadius: 10,
            data: montos
        }]
    },
    options: {
        maintainAspectRatio: false,
        layout: { padding: { left:10, right:20, top:15, bottom:0 } },
        scales: {
            xAxes: [{ gridLines: { display:false, drawBorder:false }, ticks: { maxTicksLimit:8, fontColor:tc.textColor } }],
            yAxes: [{ ticks: { maxTicksLimit:5, padding:10, fontColor:tc.textColor,
                callback: function(v){ return '$'+v.toLocaleString('es-CO'); } },
                gridLines: { color:tc.gridColor, zeroLineColor:tc.gridColor, drawBorder:false, borderDash:[2] } }]
        },
        legend: { display:false },
        tooltips: {
            backgroundColor:'rgba(30,30,46,0.95)', titleFontColor:'#E8E8ED', bodyFontColor:'#9CA3AF',
            borderColor: tc.primary, borderWidth:1,
            xPadding:14, yPadding:10, displayColors:false, intersect:false, mode:'index', caretPadding:8,
            callbacks: { label: function(item){ return ' $'+parseFloat(item.yLabel).toLocaleString('es-CO'); } }
        }
    }
});

// ---- Gráfico de inventario dinámico ----
var dataMasStock    = @json($productosMasStock);
var dataMenosStock  = @json($productosStockBajo);
var stockChartInst  = null;

function renderStockChart(data, label) {
    var ctx = document.getElementById('dynamicStockChart');
    if (stockChartInst) stockChartInst.destroy();
    stockChartInst = new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: data.map(function(p){ return p.nombre; }),
            datasets: [{ label:label, data: data.map(function(p){ return p.cantidad; }),
                backgroundColor: tc.info+'CC', borderColor: tc.info, borderWidth:1.5 }]
        },
        options: {
            maintainAspectRatio:false,
            scales: {
                xAxes: [{ ticks:{ beginAtZero:true, fontColor:tc.textColor }, gridLines:{ display:false, drawBorder:false } }],
                yAxes: [{ ticks:{ fontColor:tc.textColor }, gridLines:{ color:tc.gridColor, drawBorder:false, borderDash:[2] } }]
            },
            legend: { display:false },
            tooltips: { backgroundColor:'rgba(30,30,46,0.95)', titleFontColor:'#E8E8ED', bodyFontColor:'#9CA3AF', displayColors:false }
        }
    });
}
renderStockChart(dataMasStock, 'Stock');
window.updateStockChart = function(type, btn) {
    btn.parentElement.querySelectorAll('.btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    renderStockChart(type === 'masStock' ? dataMasStock : dataMenosStock, 'Stock');
};

// ---- Gráfico de productos vendidos dinámico ----
var dataMasVendidos    = @json($productosMasVendidos);
var dataMenosVendidos  = @json($productosMenosVendidos);
var salesChartInst     = null;

function renderSalesChart(data, label) {
    var ctx = document.getElementById('dynamicSalesChart');
    if (salesChartInst) salesChartInst.destroy();
    var bgColors = [tc.primary, tc.accent+'CC', tc.success+'CC', tc.info+'CC', tc.warning+'CC', '#2D3A3ACC'];
    salesChartInst = new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: data.map(function(p){ return p.nombre; }),
            datasets: [{ label:label, data: data.map(function(p){ return p.total_vendido; }),
                backgroundColor: bgColors.slice(0, data.length), borderWidth:0 }]
        },
        options: {
            maintainAspectRatio:false,
            scales: {
                xAxes: [{ ticks:{ beginAtZero:true, fontColor:tc.textColor }, gridLines:{ display:false, drawBorder:false } }],
                yAxes: [{ ticks:{ fontColor:tc.textColor }, gridLines:{ color:tc.gridColor, drawBorder:false, borderDash:[2] } }]
            },
            legend: { display:false },
            tooltips: { backgroundColor:'rgba(30,30,46,0.95)', titleFontColor:'#E8E8ED', bodyFontColor:'#9CA3AF', displayColors:false }
        }
    });
}
renderSalesChart(dataMasVendidos, 'Vendidos');
window.updateSalesChart = function(type, btn) {
    btn.parentElement.querySelectorAll('.btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    renderSalesChart(type === 'masVendidos' ? dataMasVendidos : dataMenosVendidos, 'Vendidos');
};

// ---- Gráfico de stock bajo ----
var stockBajo = @json($productosStockBajo);
new Chart(document.getElementById('productosStockBajoChart'), {
    type: 'horizontalBar',
    data: {
        labels: stockBajo.map(function(p){ return p.nombre || p['nombre']; }),
        datasets: [{ label:'Stock', data: stockBajo.map(function(p){ return p.cantidad || p['cantidad']; }),
            backgroundColor: tc.danger+'BB', borderColor: tc.danger, borderWidth:1.5 }]
    },
    options: {
        maintainAspectRatio:false,
        scales: {
            xAxes: [{ ticks:{ beginAtZero:true, fontColor:tc.textColor }, gridLines:{ display:false, drawBorder:false } }],
            yAxes: [{ ticks:{ fontColor:tc.textColor }, gridLines:{ color:tc.gridColor, drawBorder:false, borderDash:[2] } }]
        },
        legend: { display:false },
        tooltips: { backgroundColor:'rgba(30,30,46,0.95)', titleFontColor:'#E8E8ED', bodyFontColor:'#9CA3AF', displayColors:false }
    }
});

// ---- Presets de fecha ----
function setDatePreset(preset) {
    var today = new Date(), s, e;
    switch(preset) {
        case 'today':     s = e = today; break;
        case 'yesterday': s = e = new Date(today); s.setDate(today.getDate()-1); break;
        case 'week':      s = new Date(today); s.setDate(today.getDate()-today.getDay()); e = today; break;
        case 'month':     s = new Date(today.getFullYear(), today.getMonth(), 1); e = today; break;
        case 'year':      s = new Date(today.getFullYear(), 0, 1); e = today; break;
        default:          s = e = today;
    }
    function fmt(d) { return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0'); }
    document.getElementById('fecha_inicio').value = fmt(s);
    document.getElementById('fecha_fin').value    = fmt(e);
    document.getElementById('preset_input').value = preset;
    document.getElementById('filterForm').submit();
}
</script>
@endpush
