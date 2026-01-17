@extends('layouts.app')

@section('title','Panel')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
<style>
    /* Dashboard Modern Styles */
    .dashboard-header {
        background: var(--color-primary);
        color: white;
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .dashboard-header h1 {
        font-weight: 800;
        margin: 0;
        font-size: 2rem;
    }

    /* Modern KPI Cards */
    .kpi-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border-left: 4px solid;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        height: 100%;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        border-radius: 0 0 0 100%;
        opacity: 0.1;
    }

    .kpi-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
    }

    .kpi-card.primary {
        border-left-color: #f59e0b;
    }

    .kpi-card.primary::before {
        background: #f59e0b;
    }

    .kpi-card.success {
        border-left-color: #059669;
    }

    .kpi-card.success::before {
        background: #059669;
    }

    .kpi-card.info {
        border-left-color: #0ea5e9;
    }

    .kpi-card.info::before {
        background: #0ea5e9;
    }

    .kpi-card.warning {
        border-left-color: #eab308;
    }

    .kpi-card.warning::before {
        background: #eab308;
    }

    .kpi-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .kpi-value {
        font-size: 2rem;
        font-weight: 900;
        color: #111827;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .kpi-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: white;
    }

    .kpi-icon.primary {
        background: var(--color-primary);
    }

    .kpi-icon.success {
        background: var(--color-success);
    }

    .kpi-icon.info {
        background: var(--color-info);
    }

    .kpi-icon.warning {
        background: var(--color-warning);
    }

    /* Date Filter Card */
    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }

    .preset-btn {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 2px solid #e5e7eb;
        background: white;
        color: #6b7280;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .preset-btn:hover {
        border-color: #f59e0b;
        color: #f59e0b;
        background: #fffbeb;
    }

    .preset-btn.active {
        border-color: #f59e0b;
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        color: white;
    }

    /* Chart Cards */
    .chart-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        height: 100%;
    }

    .chart-card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(180deg, white 0%, #f9fafb 100%);
    }

    .chart-card-header h6 {
        font-weight: 700;
        color: #111827;
        margin: 0;
        font-size: 1.125rem;
    }

    .chart-card-body {
        padding: 1.5rem;
    }

    @media (min-width: 1200px) {
        .col-custom-40 {
            flex: 0 0 40%;
            max-width: 40%;
        }
        .col-custom-30 {
            flex: 0 0 30%;
            max-width: 30%;
        }
    }

    /* Animations */
    @keyframes countUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .kpi-value {
        animation: countUp 0.5s ease-out;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-2">

    
    <!-- Filtros de Fecha Mejorados -->
    <div class="filter-card">
        <form action="{{ route('panel') }}" method="GET" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label fw-semibold small text-secondary">Fecha Inicio</label>
                    <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" value="{{ $fechaInicio }}">
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label fw-semibold small text-secondary">Fecha Fin</label>
                    <input type="date" class="form-control" name="fecha_fin" id="fecha_fin" value="{{ $fechaFin }}">
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="preset-btn" onclick="setDatePreset('today')">Hoy</button>
                        <button type="button" class="preset-btn" onclick="setDatePreset('week')">Esta Semana</button>
                        <button type="button" class="preset-btn" onclick="setDatePreset('month')">Este Mes</button>
                        <button type="button" class="preset-btn" onclick="setDatePreset('year')">Este Año</button>
                        <button type="submit" class="btn btn-modern-primary ms-auto">
                            <i class="fas fa-filter me-2"></i>Filtrar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Métricas Principales (KPIs) -->
    <div class="row g-4 mb-4">
        <!-- Ventas Hoy -->
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Ventas Hoy</div>
                        <div class="kpi-value">${{ number_format($ventasHoy, 0, ',', '.') }}</div>
                        <div class="small text-success fw-semibold">
                            <i class="fas fa-arrow-up me-1"></i>+12.5% vs ayer
                        </div>
                    </div>
                    <div class="kpi-icon primary">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ventas Mes -->
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card success">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Ventas del Mes</div>
                        <div class="kpi-value">${{ number_format($ventasMes, 0, ',', '.') }}</div>
                        <div class="small text-success fw-semibold">
                            <i class="fas fa-arrow-up me-1"></i>+8.2% vs mes anterior
                        </div>
                    </div>
                    <div class="kpi-icon success">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Clientes -->
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card info">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Total Clientes</div>
                        <div class="kpi-value">{{ number_format($totalClientes, 0, ',', '.') }}</div>
                        <div class="small text-info fw-semibold">
                            <i class="fas fa-user-plus me-1"></i>Clientes activos
                        </div>
                    </div>
                    <div class="kpi-icon info">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Productos -->
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="kpi-label">Productos</div>
                        <div class="kpi-value">{{ number_format($totalProductos, 0, ',', '.') }}</div>
                        <div class="small text-warning fw-semibold">
                            <i class="fas fa-boxes me-1"></i>En inventario
                        </div>
                    </div>
                    <div class="kpi-icon warning">
                        <i class="fas fa-box-open"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row g-4">
        <!-- Gráfico de Ventas (40%) -->
        <div class="col-custom-40 col-lg-12 mb-4">
            <div class="chart-card">
                <div class="chart-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6><i class="fas fa-chart-area me-2 text-primary"></i>Resumen de Ventas</h6>
                        <div class="small text-muted">Últimos 7 días</div>
                    </div>
                </div>
                <div class="chart-card-body">
                    <div class="chart-area" style="height: 350px;">
                        <canvas id="ventasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas de Inventario (30%) -->
        <div class="col-custom-30 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Estadísticas de Inventario</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        <div class="btn-group" role="group" aria-label="Filtros Inventario">
                            <button type="button" class="btn btn-sm btn-outline-primary active" onclick="updateStockChart('masStock', this)">Más Stock</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="updateStockChart('menosStock', this)">Menos Stock</button>
                        </div>
                    </div>
                    <div class="chart-bar pt-2 pb-2" style="height: 300px;">
                        <canvas id="dynamicStockChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas de Ventas (30%) -->
        <div class="col-custom-30 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Estadísticas de Ventas</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        <div class="btn-group" role="group" aria-label="Filtros Ventas">
                            <button type="button" class="btn btn-sm btn-outline-primary active" onclick="updateSalesChart('masVendidos', this)">Más Vendidos</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="updateSalesChart('menosVendidos', this)">Menos Vendidos</button>
                        </div>
                    </div>
                    <div class="chart-pie pt-2 pb-2" style="height: 300px;">
                        <canvas id="dynamicSalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas y Stock Bajo -->
    <div class="row">
        <!-- Últimas Transacciones -->
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Últimas 5 Transacciones</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Vendedor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ultimasVentas as $venta)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($venta->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>{{ $venta->cliente ? $venta->cliente->persona->razon_social : 'Cliente General' }}</td>
                                    <td>${{ number_format($venta->total, 2) }}</td>
                                    <td>{{ $venta->user->name }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Bajo -->
        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Alerta de Stock Bajo</h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="productosStockBajoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('js')
<script src="{{ asset('js/Chart.min.js') }}"></script>
<script src="{{ asset('js/simple-datatables.min.js') }}" crossorigin="anonymous"></script>
<script src="{{ asset('js/datatables-simple-demo.js') }}"></script>

<script>
    // Configuración común para gráficos
    Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.global.defaultFontColor = '#858796';

    // --- Gráfico de Ventas ---
    let datosVenta = @json($totalVentasPorDia);
    const fechas = datosVenta.map(venta => {
        if (!venta.fecha) return 'Sin fecha';
        
        // Convertir a string si no lo es
        let fechaStr = typeof venta.fecha === 'string' ? venta.fecha : venta.fecha.toString();
        
        // Extraer solo la parte de fecha si viene con hora (YYYY-MM-DD HH:MM:SS)
        fechaStr = fechaStr.split(' ')[0];
        
        // Dividir por guión
        const partes = fechaStr.split('-');
        if (partes.length === 3) {
            const [year, month, day] = partes;
            return `${day}/${month}/${year}`;
        }
        
        return fechaStr; // Si no se puede parsear, devolver como está
    });
    const montos = datosVenta.map(venta => parseFloat(venta.total));

    new Chart(document.getElementById('ventasChart'), {
        type: 'line',
        data: {
            labels: fechas,
            datasets: [{
                label: "Ventas",
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: montos,
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
            scales: {
                xAxes: [{
                    time: { unit: 'date' },
                    gridLines: { display: false, drawBorder: false },
                    ticks: { maxTicksLimit: 7 }
                }],
                yAxes: [{
                    ticks: { maxTicksLimit: 5, padding: 10, callback: function(value) { return '$' + value; } },
                    gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] }
                }],
            },
            legend: { display: false },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, chart) {
                        var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                        return datasetLabel + ': $' + tooltipItem.yLabel.toFixed(2);
                    }
                }
            }
        }
    });

    // --- Gráfico de Inventario (Stock) ---
    const dataMasStock = @json($productosMasStock);
    const dataMenosStock = @json($productosMenosStock);
    let stockChartInstance = null;

    function renderStockChart(data, label) {
        const ctx = document.getElementById('dynamicStockChart');
        if (stockChartInstance) stockChartInstance.destroy();

        stockChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(p => p.nombre),
                datasets: [{
                    label: label,
                    data: data.map(p => p.cantidad),
                    backgroundColor: "#4e73df",
                    hoverBackgroundColor: "#2e59d9",
                    borderColor: "#4e73df",
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
                scales: {
                    xAxes: [{ gridLines: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 6 } }],
                    yAxes: [{ ticks: { beginAtZero: true, maxTicksLimit: 5, padding: 10 }, gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] } }],
                },
                legend: { display: false },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
            }
        });
    }

    renderStockChart(dataMasStock, 'Stock');

    window.updateStockChart = function(type, btn) {
        btn.parentElement.querySelectorAll('.btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        if (type === 'masStock') renderStockChart(dataMasStock, 'Stock');
        else if (type === 'menosStock') renderStockChart(dataMenosStock, 'Stock');
    };

    // --- Gráfico de Ventas (Productos) ---
    const dataMasVendidos = @json($productosMasVendidos);
    const dataMenosVendidos = @json($productosMenosVendidos);
    let salesChartInstance = null;

    function renderSalesChart(data, label, type = 'doughnut') {
        const ctx = document.getElementById('dynamicSalesChart');
        if (salesChartInstance) salesChartInstance.destroy();

        const colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'];
        const hoverColors = ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'];

        salesChartInstance = new Chart(ctx, {
            type: type,
            data: {
                labels: data.map(p => p.nombre),
                datasets: [{
                    label: label,
                    data: data.map(p => p.total_vendido),
                    backgroundColor: colors.slice(0, data.length),
                    hoverBackgroundColor: hoverColors.slice(0, data.length),
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: { display: false },
                cutoutPercentage: type === 'doughnut' ? 80 : 0,
                scales: type === 'bar' ? { yAxes: [{ ticks: { beginAtZero: true } }] } : {}
            },
        });
    }

    renderSalesChart(dataMasVendidos, 'Vendidos', 'bar');

    window.updateSalesChart = function(type, btn) {
        btn.parentElement.querySelectorAll('.btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        if (type === 'masVendidos') renderSalesChart(dataMasVendidos, 'Vendidos', 'bar');
        else if (type === 'menosVendidos') renderSalesChart(dataMenosVendidos, 'Vendidos', 'bar');
    };

    // --- Gráfico de Stock Bajo (Alert) ---
    let stockBajo = @json($productosStockBajo);
    new Chart(document.getElementById('productosStockBajoChart'), {
        type: 'horizontalBar',
        data: {
            labels: stockBajo.map(p => p.nombre),
            datasets: [{
                label: 'Stock',
                backgroundColor: "#e74a3b",
                hoverBackgroundColor: "#be2617",
                borderColor: "#e74a3b",
                data: stockBajo.map(p => p.cantidad),
            }]
        },
        options: {
            maintainAspectRatio: false,
            layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
            scales: {
                xAxes: [{ ticks: { beginAtZero: true }, gridLines: { display: false, drawBorder: false } }],
                yAxes: [{ gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] } }],
            },
            legend: { display: false },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            }
        }
    });

    // Date Preset Functions
    function setDatePreset(preset) {
        const today = new Date();
        let startDate, endDate;

        switch(preset) {
            case 'today':
                startDate = endDate = today;
                break;
            case 'week':
                startDate = new Date(today);
                startDate.setDate(today.getDate() - today.getDay());
                endDate = today;
                break;
            case 'month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = today;
                break;
            case 'year':
                startDate = new Date(today.getFullYear(), 0, 1);
                endDate = today;
                break;
        }

        document.getElementById('fecha_inicio').value = formatDate(startDate);
        document.getElementById('fecha_fin').value = formatDate(endDate);
        document.getElementById('filterForm').submit();
    }

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
</script>
@endpush

