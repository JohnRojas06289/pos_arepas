<?php

namespace App\Http\Middleware;

use App\Models\Producto;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProductoInicializado
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Permitir acceso siempre: el controller distingue entre
        // inicialización (nueva) y reinicialización (ya existe inventario)
        Producto::findOrfail($request->producto_id); // valida que el producto existe
        return $next($request);
    }
}
