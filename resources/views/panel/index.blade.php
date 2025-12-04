@extends('layouts.app')

@section('title','Panel')

@push('css')
<link href="{{ asset('js/simple-datatables.min.js') }}/dist/style.css" rel="stylesheet" />
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
<style>
    .card-metric {
        transition: transform 0.2s;
    }
    .card-metric:hover {
        transform: translateY(-5px);
    }
    .icon-box {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.5rem;
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
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Panel de Control</h1>
    
    <!-- Filtros de Fecha -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('panel') }}" method="GET" class="row align-items-end">
                <div class="col-md-4">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" name="fecha_inicio" value="{{ $fechaInicio }}">
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" name="fecha_fin" value="{{ $fechaFin }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Métricas Principales -->
    <div class="row">
        <!-- Ventas Hoy -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 card-metric">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Ventas Hoy</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($ventasHoy, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-box bg-primary text-white">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ventas Mes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 card-metric">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Ventas del Mes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($ventasMes, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-box bg-success text-white">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Clientes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 card-metric">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Clientes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalClientes }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-box bg-info text-white">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Productos -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 card-metric">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Productos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalProductos }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-box bg-warning text-white">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <!-- Gráfico de Ventas (40%) -->
        <div class="col-custom-40 col-lg-12 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Resumen de Ventas</h6>
                </div>
                <div class="card-body">
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
</script>
@endpush
