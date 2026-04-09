<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Rules\CajaCerradaRule;
use App\Services\ActivityLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class CajaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $cajas = Caja::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('caja.index', compact('cajas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('caja.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'saldo_inicial' => ['required', 'numeric', 'min:1', new CajaCerradaRule]
        ]);
        try {
            $caja = Caja::create($request->all());
            ActivityLogService::log('Creación de caja', 'Cajas', ['caja' => $caja]);
            return redirect()->route('movimientos.index', ['caja_id' => $caja->id])->with('success', 'Caja aperturada');
        } catch (Throwable $e) {
            Log::error('Error al crear la caja', ['error' => $e->getMessage()]);
            return redirect()->route('cajas.index')->with('error', 'Ups, algo falló');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Resumen de ventas de una caja: por producto y por método de pago.
     */
    public function resumen(Caja $caja): JsonResponse
    {
        $ventas = $caja->ventas()->where('revertida', 0)->with('productos')->get();

        // Agrupar por producto
        $porProducto = [];
        foreach ($ventas as $venta) {
            foreach ($venta->productos as $producto) {
                $id = $producto->id;
                if (!isset($porProducto[$id])) {
                    $porProducto[$id] = [
                        'nombre'   => $producto->nombre,
                        'cantidad' => 0,
                        'total'    => 0,
                    ];
                }
                $porProducto[$id]['cantidad'] += $producto->pivot->cantidad;
                $porProducto[$id]['total']    += $producto->pivot->cantidad * $producto->pivot->precio_venta;
            }
        }

        usort($porProducto, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));

        // Agrupar por método de pago
        $porMetodo = ['EFECTIVO' => 0, 'NEQUI' => 0, 'DAVIPLATA' => 0];
        foreach ($ventas as $venta) {
            $metodo = strtoupper($venta->metodo_pago ?? '');
            if (array_key_exists($metodo, $porMetodo)) {
                $porMetodo[$metodo] += $venta->total;
            }
        }

        return response()->json([
            'por_producto'  => array_values($porProducto),
            'por_metodo'    => $porMetodo,
            'total_general' => $ventas->sum('total'),
            'num_ventas'    => $ventas->count(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Caja $caja): RedirectResponse
    {
        try {
            $caja->update(['estado' => 0]);
            ActivityLogService::log('Caja cerrada', 'Cajas', ['estado' => $caja->estado]);
            return redirect()->route('cajas.index')->with('success', 'Caja cerrada');
        } catch (Throwable $e) {
            Log::error('Error al cerrar la caja', ['error' => $e->getMessage()]);
            return redirect()->route('cajas.index')->with('error', 'Ups, algo falló');
        }
    }
}
