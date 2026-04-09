<?php

namespace App\Http\Controllers;

use App\Enums\CategoriaGastoEnum;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Gasto;
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

            $ventasPorCliente = Venta::with(['user', 'cliente.persona', 'productos'])
                ->whereBetween('created_at', [$hoyInicio, $hoyFin])
                ->get()
                ->groupBy('cliente_id');

            return view('panel.index', compact(
                'ventasHoy',
                'ventasEfectivo',
                'ventasNequi',
                'ventasDaviplata',
                'ventasFiado',
                'ventasPorCliente'
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
            $fechaFin    = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

            $periodoInicio = $fechaInicio . ' 00:00:00';
            $periodoFin    = $fechaFin    . ' 23:59:59';

            // Totales del período por método de pago
            $ventasPeriodo = Venta::whereBetween('created_at', [$periodoInicio, $periodoFin])->sum('total');

            $ventasPorMetodo = Venta::whereBetween('created_at', [$periodoInicio, $periodoFin])
                ->select('metodo_pago', DB::raw('SUM(total) as total'))
                ->groupBy('metodo_pago')
                ->pluck('total', 'metodo_pago');

            $ventasEfectivo  = $ventasPorMetodo['EFECTIVO']  ?? 0;
            $ventasNequi     = $ventasPorMetodo['NEQUI']     ?? 0;
            $ventasDaviplata = $ventasPorMetodo['DAVIPLATA'] ?? 0;
            $ventasFiado     = $ventasPorMetodo['FIADO']     ?? 0;

            $totalClientes  = Cliente::count();
            $totalProductos = Producto::count();
            $totalCompras   = Compra::count();
            $totalUsuarios  = User::count();

            // Gráfica de ventas por día (período filtrado)
            $totalVentasPorDia = DB::table('ventas')
                ->selectRaw('DATE(created_at) as fecha, SUM(total) as total')
                ->whereBetween('created_at', [$periodoInicio, $periodoFin])
                ->groupByRaw('DATE(created_at)')
                ->orderBy('fecha', 'asc')
                ->get()
                ->toArray();

            // Top 3 productos más vendidos
            $productosMasVendidos = DB::table('producto_venta')
                ->join('productos', 'producto_venta.producto_id', '=', 'productos.id')
                ->select('productos.nombre', DB::raw('SUM(producto_venta.cantidad) as total_vendido'))
                ->groupBy('productos.id', 'productos.nombre')
                ->orderByDesc('total_vendido')
                ->limit(3)
                ->get();

            // Top 3 productos menos vendidos
            $productosMenosVendidos = DB::table('producto_venta')
                ->join('productos', 'producto_venta.producto_id', '=', 'productos.id')
                ->select('productos.nombre', DB::raw('SUM(producto_venta.cantidad) as total_vendido'))
                ->groupBy('productos.id', 'productos.nombre')
                ->orderBy('total_vendido', 'asc')
                ->limit(3)
                ->get();

            // Top 5 con más stock
            $productosMasStock = DB::table('productos')
                ->join('inventario', 'productos.id', '=', 'inventario.producto_id')
                ->select('productos.nombre', 'inventario.cantidad')
                ->orderByDesc('inventario.cantidad')
                ->limit(5)
                ->get();

            // Top 5 con menos stock (excluyendo sin stock)
            $productosStockBajo = DB::table('productos')
                ->join('inventario', 'productos.id', '=', 'inventario.producto_id')
                ->select('productos.nombre', 'inventario.cantidad')
                ->where('inventario.cantidad', '>', 0)
                ->orderBy('inventario.cantidad', 'asc')
                ->limit(5)
                ->get();

            $ventasPorCliente = Venta::with(['user', 'cliente.persona'])
                ->whereBetween('fecha_hora', [$fechaInicio, $fechaFin])
                ->get()
                ->groupBy('cliente_id');

            // ── Sección financiera ────────────────────────────────────────
            // Ingresos: solo ventas cobradas (pagado=1, revertida=0)
            $ingresosPeriodo = Venta::whereBetween('created_at', [$periodoInicio, $periodoFin])
                ->where('pagado', 1)
                ->where('revertida', 0)
                ->sum('total');

            // Costo de mercancía: compras del período
            $costoCompras = Compra::whereBetween('fecha_hora', [$periodoInicio, $periodoFin])
                ->where('user_id', Auth::id())
                ->sum('total');

            // Gastos del negocio: tabla gastos del período
            $gastosNegocio = Gasto::whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->where('user_id', Auth::id())
                ->sum('monto');

            // Breakdown gastos por categoría
            $gastosPorCategoria = Gasto::whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->where('user_id', Auth::id())
                ->select('categoria', DB::raw('SUM(monto) as total'))
                ->groupBy('categoria')
                ->orderByDesc('total')
                ->get()
                ->map(fn($g) => [
                    'label' => CategoriaGastoEnum::from($g->categoria)->label(),
                    'color' => CategoriaGastoEnum::from($g->categoria)->color(),
                    'total' => (float) $g->total,
                ]);

            $gananciaBruta = $ingresosPeriodo - $costoCompras;
            $gananciaNeta  = $ingresosPeriodo - $costoCompras - $gastosNegocio;
            $margenNeto    = $ingresosPeriodo > 0
                ? round(($gananciaNeta / $ingresosPeriodo) * 100, 1)
                : 0;

            return view('admin.estadisticas.index', compact(
                'ventasPeriodo',
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
                'productosStockBajo',
                'ventasPorCliente',
                'fechaInicio',
                'fechaFin',
                'ingresosPeriodo',
                'costoCompras',
                'gastosNegocio',
                'gastosPorCategoria',
                'gananciaBruta',
                'gananciaNeta',
                'margenNeto'
            ));
        } catch (\Throwable $e) {
            Log::error('Error en Estadísticas', ['error' => $e->getMessage()]);
            return redirect()->route('panel')->with('error', 'Ocurrió un error cargando las estadísticas.');
        }
    }
}
