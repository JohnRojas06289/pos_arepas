<?php

namespace App\Http\Middleware;

use App\Models\Caja;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckMovimientoCajaUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->caja_id) {
            $cajaAbierta = Caja::where('user_id', Auth::id())->where('estado', 1)->first();
            if ($cajaAbierta) {
                return redirect()->route('movimientos.index', ['caja_id' => $cajaAbierta->id]);
            }
            return redirect()->route('cajas.index')->with('error', 'Selecciona una caja para ver los movimientos.');
        }

        $caja = Caja::findOrfail($request->caja_id);
        if ($caja->user_id != Auth::id()) {
            throw new HttpException(401, 'No autorizado');
        }
        return $next($request);
    }
}
