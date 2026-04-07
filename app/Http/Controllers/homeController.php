<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class homeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (!Auth::user()->can('ver-panel')) {
            return redirect()->route('ventas.create');
        }

        try {
            $hoyInicio = Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
            $hoyFin = Carbon::now()->endOfDay()->format('Y-m-d H:i:s');

            $ventasDelDia = Venta::with(['user', 'cliente.persona'])
                ->noRevertidas()
                ->whereBetween('fecha_hora', [$hoyInicio, $hoyFin])
                ->orderByDesc('fecha_hora')
                ->get();

            $ventasHoy = (float) $ventasDelDia->sum('total');
            $transaccionesHoy = $ventasDelDia->count();
            $clientesAtendidos = $ventasDelDia
                ->pluck('cliente_id')
                ->filter()
                ->unique()
                ->count();
            $ticketPromedio = $transaccionesHoy > 0 ? round($ventasHoy / $transaccionesHoy, 2) : 0;
            $ultimasVentas = $ventasDelDia->take(8);

            return view('panel.index', compact(
                'ventasHoy',
                'transaccionesHoy',
                'clientesAtendidos',
                'ticketPromedio',
                'ultimasVentas'
            ));
        } catch (\Throwable $e) {
            Log::error('Error en Dashboard', ['error' => $e->getMessage()]);

            return redirect()->route('panel')->with('error', 'Ocurrió un error cargando el panel.');
        }
    }

    public function estadisticas(Request $request): View|RedirectResponse
    {
        if (!Auth::user()->hasRole('administrador')) {
            return redirect()->route('panel')->with('error', 'Acceso denegado');
        }

        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subDays(7)->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

            $periodoInicio = $fechaInicio . ' 00:00:00';
            $periodoFin = $fechaFin . ' 23:59:59';

            $ventasPeriodo = Venta::noRevertidas()
                ->whereBetween('fecha_hora', [$periodoInicio, $periodoFin])
                ->sum('total');

            $ventasPorMetodo = Venta::noRevertidas()
                ->whereBetween('fecha_hora', [$periodoInicio, $periodoFin])
                ->select('metodo_pago', DB::raw('SUM(total) as total'))
                ->groupBy('metodo_pago')
                ->pluck('total', 'metodo_pago');

            $ventasEfectivo = $ventasPorMetodo['EFECTIVO'] ?? 0;
            $ventasNequi = $ventasPorMetodo['NEQUI'] ?? 0;
            $ventasDaviplata = $ventasPorMetodo['DAVIPLATA'] ?? 0;

            $totalClientes = Cliente::count();
            $totalProductos = Producto::count();
            $totalCompras = Compra::count();
            $totalUsuarios = User::count();

            $totalVentasPorDia = DB::table('ventas')
                ->selectRaw('DATE(fecha_hora) as fecha, SUM(total) as total')
                ->where(function ($query) {
                    $query->where('revertida', false)
                        ->orWhereNull('revertida');
                })
                ->whereBetween('fecha_hora', [$periodoInicio, $periodoFin])
                ->groupByRaw('DATE(fecha_hora)')
                ->orderBy('fecha', 'asc')
                ->get()
                ->toArray();

            $productosMasVendidos = DB::table('producto_venta')
                ->join('productos', 'producto_venta.producto_id', '=', 'productos.id')
                ->select('productos.nombre', DB::raw('SUM(producto_venta.cantidad) as total_vendido'))
                ->groupBy('productos.id', 'productos.nombre')
                ->orderByDesc('total_vendido')
                ->limit(3)
                ->get();

            $productosMenosVendidos = DB::table('producto_venta')
                ->join('productos', 'producto_venta.producto_id', '=', 'productos.id')
                ->select('productos.nombre', DB::raw('SUM(producto_venta.cantidad) as total_vendido'))
                ->groupBy('productos.id', 'productos.nombre')
                ->orderBy('total_vendido', 'asc')
                ->limit(3)
                ->get();

            $productosMasStock = DB::table('productos')
                ->join('inventario', 'productos.id', '=', 'inventario.producto_id')
                ->select('productos.nombre', 'inventario.cantidad')
                ->orderByDesc('inventario.cantidad')
                ->limit(5)
                ->get();

            $productosStockBajo = DB::table('productos')
                ->join('inventario', 'productos.id', '=', 'inventario.producto_id')
                ->select('productos.nombre', 'inventario.cantidad')
                ->where('inventario.cantidad', '>', 0)
                ->orderBy('inventario.cantidad', 'asc')
                ->limit(5)
                ->get();

            $ventasPorCliente = Venta::with(['user', 'cliente.persona'])
                ->noRevertidas()
                ->whereBetween('fecha_hora', [$periodoInicio, $periodoFin])
                ->get()
                ->groupBy('cliente_id');

            return view('admin.estadisticas.index', compact(
                'ventasPeriodo',
                'ventasEfectivo',
                'ventasNequi',
                'ventasDaviplata',
                'totalClientes',
                'totalProductos',
                'totalCompras',
                'totalUsuarios',
                'totalVentasPorDia',
                'productosMasVendidos',
                'productosMenosVendidos',
                'productosMasStock',
                'productosStockBajo',
                'ventasPorCliente',
                'fechaInicio',
                'fechaFin'
            ));
        } catch (\Throwable $e) {
            Log::error('Error en Estadísticas', ['error' => $e->getMessage()]);

            return redirect()->route('panel')->with('error', 'Ocurrió un error cargando las estadísticas.');
        }
    }
}
