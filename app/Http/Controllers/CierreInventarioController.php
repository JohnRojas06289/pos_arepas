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

        try {
            DB::transaction(function () use ($caja, $items, $kardex) {
                CierreInventario::create([
                    'caja_id' => $caja->id,
                    'user_id' => auth()->id(),
                    'items'   => $items,
                ]);

                foreach ($items as $item) {
                    if ($item['cantidad_fisica'] === null) {
                        continue;
                    }

                    $inventario = Inventario::firstOrCreate(
                        ['producto_id' => $item['producto_id']],
                        ['cantidad' => 0]
                    );

                    $cantidadAnterior = (int) $inventario->cantidad;
                    $nuevaCantidad    = (int) $item['cantidad_fisica'];

                    // Solo actualizar si la cantidad realmente cambió
                    if ($nuevaCantidad !== $cantidadAnterior) {
                        $costoUnitario = Kardex::where('producto_id', $item['producto_id'])
                            ->latest('id')
                            ->value('costo_unitario') ?? 0;

                        // Registrar ajuste en Kardex para mantener consistencia
                        $kardex->crearRegistro(
                            [
                                'producto_id'    => $item['producto_id'],
                                'cantidad'       => $nuevaCantidad,
                                'costo_unitario' => $costoUnitario,
                            ],
                            TipoTransaccionEnum::Apertura
                        );

                        $inventario->update(['cantidad' => $nuevaCantidad]);
                    }
                }
            });

            $contados = count(array_filter($items, fn($i) => $i['cantidad_fisica'] !== null));
            $ajustados = count(array_filter($items, fn($i) => $i['cantidad_fisica'] !== null && $i['diferencia'] !== 0));

            ActivityLogService::log('Cierre de inventario', 'Inventario', [
                'caja_id'  => $caja->id,
                'contados' => $contados,
                'ajustados' => $ajustados,
            ]);

            return redirect()->route('cajas.index')
                ->with('success', "Cierre guardado. {$contados} productos contados, {$ajustados} ajustados.");
        } catch (Throwable $e) {
            Log::error('Error al guardar cierre de inventario', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ups, algo falló al guardar el cierre. Intenta de nuevo.');
        }
    }

    public function show(CierreInventario $cierre): View
    {
        return view('caja.cierre-inventario-ver', compact('cierre'));
    }
}
