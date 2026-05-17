<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\EmpresaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    protected EmpresaService $empresaService;

    public function __construct(EmpresaService $empresaService)
    {
        $this->middleware('permission:crear-pedido', ['only' => ['create', 'store']]);
        $this->middleware('permission:crear-venta',  ['only' => ['pendientes', 'tomar']]);

        $this->empresaService = $empresaService;
    }

    public function create(): View
    {
        $empresa = $this->empresaService->obtenerEmpresa();

        $productos = Producto::leftJoin('inventario as i', 'i.producto_id', '=', 'productos.id')
            ->leftJoin('presentaciones as p', 'p.id', '=', 'productos.presentacione_id')
            ->select(
                DB::raw("COALESCE(p.sigla, 'UND') as sigla"),
                'productos.nombre',
                'productos.codigo',
                'productos.id',
                DB::raw("COALESCE(i.cantidad, 0) as cantidad"),
                'productos.precio',
                'productos.img_path',
                'productos.categoria_id'
            )
            ->where('productos.estado', 1)
            ->orderBy('productos.nombre', 'asc')
            ->get();

        $categorias = Cache::remember('categorias_activas_sorted', 3600, function () {
            return Categoria::with('caracteristica')
                ->join('caracteristicas as c', 'categorias.caracteristica_id', '=', 'c.id')
                ->where('c.estado', 1)
                ->orderBy('c.nombre', 'asc')
                ->select('categorias.*')
                ->get();
        });

        return view('pedidos.create', compact('productos', 'categorias', 'empresa'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'total' => ['required', 'numeric'],
        ]);

        Pedido::create([
            'user_id'        => auth()->id(),
            'nombre_tomador' => auth()->user()->name,
            'items'          => $request->items,
            'total'          => $request->total,
            'estado'         => 'pendiente',
        ]);

        return response()->json(['message' => 'Pedido enviado'], 201);
    }

    public function pendientes(): JsonResponse
    {
        $pedidos = Pedido::where('estado', 'pendiente')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn (Pedido $p) => [
                'id'              => $p->id,
                'nombre_tomador'  => $p->nombre_tomador,
                'items'           => $p->items,
                'total'           => $p->total,
                'created_at_human'=> $p->created_at->diffForHumans(),
            ]);

        return response()->json($pedidos);
    }

    public function tomar(Pedido $pedido): JsonResponse
    {
        $pedido->estado = 'tomado';
        $pedido->save();

        return response()->json([
            'items'          => $pedido->items,
            'nombre_tomador' => $pedido->nombre_tomador,
        ]);
    }
}
