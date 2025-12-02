@extends('layouts.app')

@section('title','Panel')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <!-- Gráfico de Ventas -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Resumen de Ventas</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 300px;">
                        <canvas id="ventasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas de Productos -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Estadísticas de Productos</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        <div class="btn-group" role="group" aria-label="Filtros Productos">
                            <button type="button" class="btn btn-sm btn-outline-primary active" onclick="updateProductChart('masVendidos', this)">Más Vendidos</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="updateProductChart('menosVendidos', this)">Menos Vendidos</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="updateProductChart('masStock', this)">Más Stock</button>
                        </div>
                    </div>
                    <div class="chart-pie pt-2 pb-2" style="height: 250px;">
                        <canvas id="dynamicProductChart"></canvas>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<script src="{{ asset('js/datatables-simple-demo.js') }}"></script>

<script>
    // Configuración común para gráficos
    Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.global.defaultFontColor = '#858796';

    // Gráfico de Ventas
    let datosVenta = @json($totalVentasPorDia);
    const fechas = datosVenta.map(venta => {
        const [year, month, day] = venta.fecha.split('-');
        return `${day}/${month}/${year}`;
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

    // Datos para el gráfico dinámico de productos
    const dataMasVendidos = @json($productosMasVendidos);
    const dataMenosVendidos = @json($productosMenosVendidos);
    const dataMasStock = @json($productosMasStock);

    let productChartInstance = null;

    function renderProductChart(data, label, type = 'doughnut') {
        const ctx = document.getElementById('dynamicProductChart');
        
        if (productChartInstance) {
            productChartInstance.destroy();
        }

        // Colores para el gráfico
        const colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'];
        const hoverColors = ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'];

        productChartInstance = new Chart(ctx, {
            type: type,
            data: {
                labels: data.map(p => p.nombre),
                datasets: [{
                    label: label,
                    data: data.map(p => p.total_vendido || p.cantidad), // Usa total_vendido o cantidad según el dataset
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
                legend: { display: false }, // Ocultar leyenda para ahorrar espacio o mostrarla si se prefiere
                cutoutPercentage: type === 'doughnut' ? 80 : 0,
                scales: type === 'bar' ? {
                    yAxes: [{
                        ticks: { beginAtZero: true }
                    }]
                } : {}
            },
        });
    }

    // Inicializar con "Más Vendidos"
    renderProductChart(dataMasVendidos, 'Vendidos');

    // Función para actualizar el gráfico desde los botones
    window.updateProductChart = function(type, btn) {
        // Actualizar estado activo de los botones
        document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        if (type === 'masVendidos') {
            renderProductChart(dataMasVendidos, 'Vendidos', 'doughnut');
        } else if (type === 'menosVendidos') {
            renderProductChart(dataMenosVendidos, 'Vendidos', 'bar'); // Bar chart para comparar mejor los bajos números
        } else if (type === 'masStock') {
            renderProductChart(dataMasStock, 'Stock', 'bar'); // Bar chart para stock
        }
    };


    // Gráfico de Stock Bajo
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
                xAxes: [{
                    ticks: { beginAtZero: true },
                    gridLines: { display: false, drawBorder: false }
                }],
                yAxes: [{
                    gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] }
                }],
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