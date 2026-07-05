<?php

namespace App\Http\Controllers;

use App\Enums\TipoTransaccionEnum;
use App\Models\Caja;
use App\Models\CierreInventario;
use App\Models\Inventario;
use App\Models\Kardex;
use App\Models\Producto;
use App\Services\ActivityLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CierreInventarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver-inventario');
    }

    public function create(Caja $caja): View
    {
        $productos = Producto::with('inventario')
            ->where('estado', 1)
            ->orderBy('nombre', 'asc')
            ->get();

        $ultimoCierre = CierreInventario::where('caja_id', $caja->id)
            ->latest()
            ->first();

        return view('caja.cierre-inventario', compact('caja', 'productos', 'ultimoCierre'));
    }

    public function store(Request $request, Caja $caja, Kardex $kardex): RedirectResponse
    {
        $request->validate([
            'items'                    => ['required', 'array'],
            'items.*.producto_id'      => ['required', 'string'],
            'items.*.nombre'           => ['required', 'string'],
            'items.*.cantidad_sistema' => ['required', 'integer', 'min:0'],
            'items.*.cantidad_fisica'  => ['nullable', 'integer', 'min:0'],
        ]);

        $items = [];
        foreach ($request->items as $raw) {
            $fisica     = isset($raw['cantidad_fisica']) && $raw['cantidad_fisica'] !== '' ? (int) $raw['cantidad_fisica'] : null;
            $sistema    = (int) $raw['cantidad_sistema'];
            $diferencia = $fisica !== null ? $fisica - $sistema : null;

            $items[] = [
                'producto_id'      => $raw['producto_id'],
                'nombre'           => $raw['nombre'],
                'cantidad_sistema' => $sistema,
                'cantidad_fisica'  => $fisica,
                'diferencia'       => $diferencia,
            ];
        }

        DB::beginTransaction();
        try {
            CierreInventario::create([
                'caja_id' => $caja->id,
                'user_id' => auth()->id(),
                'items'   => $items,
            ]);

            $contados  = 0;
            $ajustados = 0;

            foreach ($items as $item) {
                if ($item['cantidad_fisica'] === null) {
                    continue;
                }

                $contados++;
                $nuevaCantidad = (int) $item['cantidad_fisica'];

                // Buscar o crear registro de inventario
                $inventario = Inventario::where('producto_id', $item['producto_id'])->first();

                if ($inventario) {
                    $cantidadAnterior = (int) $inventario->cantidad;
                    // Actualizar siempre que el usuario haya ingresado un conteo
                    DB::table('inventario')
                        ->where('producto_id', $item['producto_id'])
                        ->update(['cantidad' => $nuevaCantidad]);
                } else {
                    $cantidadAnterior = 0;
                    Inventario::create([
                        'producto_id' => $item['producto_id'],
                        'cantidad'    => $nuevaCantidad,
                    ]);
                }

                // Registrar en Kardex para mantener el saldo sincronizado
                $costoUnitario = Kardex::where('producto_id', $item['producto_id'])
                    ->latest('id')
                    ->value('costo_unitario') ?? 0;

                $kardex->crearRegistro(
                    [
                        'producto_id'    => $item['producto_id'],
                        'cantidad'       => $nuevaCantidad,
                        'costo_unitario' => $costoUnitario,
                    ],
                    TipoTransaccionEnum::Apertura
                );

                if ($nuevaCantidad !== $cantidadAnterior) {
                    $ajustados++;
                }
            }

            DB::commit();

            ActivityLogService::log('Cierre de inventario', 'Inventario', [
                'caja_id'   => $caja->id,
                'contados'  => $contados,
                'ajustados' => $ajustados,
            ]);

            return redirect()->route('cajas.index')
                ->with('success', "Cierre guardado. {$contados} productos contados, {$ajustados} ajustados.");
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al guardar cierre de inventario', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ups, algo falló al guardar el cierre: ' . $e->getMessage());
        }
    }

    public function show(CierreInventario $cierre): View
    {
        return view('caja.cierre-inventario-ver', compact('cierre'));
    }
}
