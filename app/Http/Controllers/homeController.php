<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class homeController extends Controller
{
    public function index(Request $request): View|RedirectResponse|\Illuminate\Http\Response
    {
        if (!Auth::check()) {
            return redirect()->route('login.index');
        }

        if (!Auth::user()->can('ver-panel')) {
            return redirect()->route('ventas.create');
        }

        try {
            // Panel simplificado: Solo mostrar las ventas de HOY
            $hoyInicio = Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
            $hoyFin    = Carbon::now()->endOfDay()->format('Y-m-d H:i:s');

            $ventasHoy = Venta::whereBetween('created_at', [$hoyInicio, $hoyFin])->sum('total');

            $ventasPorMetodo = Venta::whereBetween('created_at', [$hoyInicio, $hoyFin])
                ->select('metodo_pago', DB::raw('SUM(total) as total'))
                ->groupBy('metodo_pago')
                ->pluck('total', 'metodo_pago');

            $ventasEfectivo  = $ventasPorMetodo['EFECTIVO']  ?? 0;
            $ventasNequi     = $ventasPorMetodo['NEQUI']     ?? 0;
            $ventasDaviplata = $ventasPorMetodo['DAVIPLATA'] ?? 0;
            $ventasFiado     = $ventasPorMetodo['FIADO']     ?? 0;
            
            // Últimas Ventas (Transacciones recientes - Limit 5)
            $ultimasVentas = Venta::with('user', 'cliente')
                ->latest()
                ->limit(5)
                ->get();

            return view('panel.index', compact(
                'ventasHoy',
                'ventasEfectivo',
                'ventasNequi',
                'ventasDaviplata',
                'ventasFiado',
                'ultimasVentas'
            ));
        } catch (\Exception $e) {
            return response("Error en Dashboard: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
        }
    }

    public function estadisticas(Request $request): View|RedirectResponse|\Illuminate\Http\Response
    {
        if (!Auth::check() || !Auth::user()->hasRole('administrador')) {
            return redirect()->route('panel')->with('error', 'Acceso denegado');
        }

        try {
            // Filtros de fecha para la gráfica de ventas
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subDays(7)->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));
    
            // Métricas Principales — totales del período filtrado por método de pago
            $periodoInicio = $fechaInicio . ' 00:00:00';
            $periodoFin    = $fechaFin    . ' 23:59:59';

            $ventasHoy = Venta::whereBetween('created_at', [$periodoInicio, $periodoFin])->sum('total');

            $ventasPorMetodo = Venta::whereBetween('created_at', [$periodoInicio, $periodoFin])
                ->select('metodo_pago', DB::raw('SUM(total) as total'))
                ->groupBy('metodo_pago')
                ->pluck('total', 'metodo_pago');

            $ventasEfectivo  = $ventasPorMetodo['EFECTIVO']  ?? 0;
            $ventasNequi     = $ventasPorMetodo['NEQUI']     ?? 0;
            $ventasDaviplata = $ventasPorMetodo['DAVIPLATA'] ?? 0;
            $ventasFiado     = $ventasPorMetodo['FIADO']     ?? 0;
            
            $totalClientes = Cliente::count();
            $totalProductos = Producto::count();
            $totalCompras = Compra::count();
            $totalUsuarios = User::count();
    
            // Gráfica de Ventas por Día (Filtrada)
            $totalVentasPorDia = DB::table('ventas')
                ->selectRaw('CAST(created_at AS DATE) as fecha, SUM(total) as total')
                ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                ->groupBy(DB::raw('CAST(created_at AS DATE)'))
                ->orderBy('fecha', 'asc')
                ->get()->toArray();
    
            // Productos más vendidos (Top 3)
            $productosMasVendidos = DB::table('producto_venta')
                ->join('productos', 'producto_venta.producto_id', '=', 'productos.id')
                ->select('productos.nombre', DB::raw('SUM(producto_venta.cantidad) as total_vendido'))
                ->groupBy('productos.id', 'productos.nombre')
                ->orderByDesc('total_vendido')
                ->limit(3)
                ->get();
    
            // Productos menos vendidos (Top 3)
            $productosMenosVendidos = DB::table('producto_venta')
                ->join('productos', 'producto_venta.producto_id', '=', 'productos.id')
                ->select('productos.nombre', DB::raw('SUM(producto_venta.cantidad) as total_vendido'))
                ->groupBy('productos.id', 'productos.nombre')
                ->orderBy('total_vendido', 'asc')
                ->limit(3)
                ->get();
    
            // Productos con Más Stock (Top 5)
            $productosMasStock = DB::table('productos')
                ->join('inventario', 'productos.id', '=', 'inventario.producto_id')
                ->orderByDesc('inventario.cantidad')
                ->select('productos.nombre', 'inventario.cantidad')
                ->limit(5)
                ->get();
    
            // Productos con Menos Stock (Top 5)
            $productosMenosStock = DB::table('productos')
                ->join('inventario', 'productos.id', '=', 'inventario.producto_id')
                ->where('inventario.cantidad', '>', 0)
                ->orderBy('inventario.cantidad', 'asc')
                ->select('productos.nombre', 'inventario.cantidad')
                ->limit(5)
                ->get();
    
            // Productos con Stock Bajo (Top 5)
            $productosStockBajo = DB::table('productos')
                ->join('inventario', 'productos.id', '=', 'inventario.producto_id')
                ->where('inventario.cantidad', '>', 0)
                ->orderBy('inventario.cantidad', 'asc')
                ->select('productos.nombre', 'inventario.cantidad')
                ->limit(5)
                ->get();
    
            return view('admin.estadisticas.index', compact(
                'ventasHoy',
                'ventasEfectivo',
                'ventasNequi',
                'ventasDaviplata',
                'ventasFiado',
                'totalClientes',
                'totalProductos',
                'totalCompras',
                'totalUsuarios',
                'totalVentasPorDia',
                'productosMasVendidos',
                'productosMenosVendidos',
                'productosMasStock',
                'productosMenosStock',
                'productosStockBajo',
                'fechaInicio',
                'fechaFin'
            ));
        } catch (\Exception $e) {
            return response("Error en Estadísticas: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
        }
    }
}
