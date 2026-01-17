<?php

namespace App\Http\Controllers;

use App\Enums\TipoTransaccionEnum;
use App\Http\Requests\StoreInventarioRequest;
use App\Models\Inventario;
use App\Models\Kardex;
use App\Models\Producto;
use App\Models\Ubicacione;
use App\Services\ActivityLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class InventarioControlller extends Controller
{
    function __construct()
    {
        $this->middleware('check_producto_inicializado', ['only' => ['create', 'store']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $categorias = \App\Models\Categoria::with('caracteristica')->get();
        
        $productos = Producto::with(['inventario', 'presentacione', 'categoria.caracteristica'])
            ->when($request->categoria_id, function($query, $categoria_id) {
                return $query->where('categoria_id', $categoria_id);
            })
            ->orderBy('nombre', 'asc')
            ->get();
            
        return view('inventario.index', compact('productos', 'categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $producto = Producto::findOrfail($request->producto_id);
        $ubicaciones = Ubicacione::all();
        return view('inventario.create', compact('producto', 'ubicaciones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventarioRequest $request, Kardex $kardex): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $kardex->crearRegistro($request->validated(), TipoTransaccionEnum::Apertura);
            Inventario::create($request->validated());

            // Update product sale price
            $producto = Producto::findOrFail($request->producto_id);
            $producto->update(['precio' => $request->precio_venta]);

            DB::commit();
            ActivityLogService::log('Inicialiación de producto', 'Productos', $request->validated());
            return redirect()->route('productos.index')->with('success', 'Producto inicializado');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al inicializar el producto', ['error' => $e->getMessage()]);
            return redirect()->route('productos.index')->with('error', 'Ups, algo falló');
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
        $inventario = Inventario::with('producto')->findOrFail($id);
        $producto = $inventario->producto;
        
        // Fetch last cost from Kardex
        $ultimoKardex = Kardex::where('producto_id', $producto->id)->latest('id')->first();
        $costo_unitario = $ultimoKardex ? $ultimoKardex->costo_unitario : 0;

        return view('inventario.edit', compact('inventario', 'producto', 'costo_unitario'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreInventarioRequest $request, string $id)
    {
        $inventario = Inventario::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Updated Product Price
            $producto = $inventario->producto;
            if ($request->has('precio_venta')) {
                $producto->update(['precio' => $request->precio_venta]);
            }

            // Update Inventory (excluding fields not in table)
            // We use except() because validated() returns fields like costo_unitario/precio_venta that don't exist in 'inventario' table
            $data = $request->safe()->except(['costo_unitario', 'precio_venta']);
            $inventario->update($data);
            
            // Note: Costo Unitario updates typically require a new Kardex entry. 
            // For now, we are prioritizing fixing the crash and price update.
            // If cost needs adjustment, a specific Kardex flow should be triggered.

            DB::commit();
            ActivityLogService::log('Actualización de inventario', 'Inventario', $request->validated());
            return redirect()->route('inventario.index')->with('success', 'Inventario actualizado');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar el inventario', ['error' => $e->getMessage()]);
            return redirect()->route('inventario.index')->with('error', 'Ups, algo falló');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $inventario = Inventario::findOrFail($id);
        DB::beginTransaction();
        try {
            $inventario->delete();
            DB::commit();
            ActivityLogService::log('Eliminación de inventario', 'Inventario', ['id' => $id]);
            return redirect()->route('inventario.index')->with('success', 'Inventario eliminado');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al eliminar el inventario', ['error' => $e->getMessage()]);
            return redirect()->route('inventario.index')->with('error', 'Ups, algo falló');
        }
    }
}
