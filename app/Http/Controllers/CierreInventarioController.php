<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\CierreInventario;
use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function store(Request $request, Caja $caja): RedirectResponse
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

        DB::transaction(function () use ($caja, $items) {
            CierreInventario::create([
                'caja_id' => $caja->id,
                'user_id' => auth()->id(),
                'items'   => $items,
            ]);

            foreach ($items as $item) {
                if ($item['cantidad_fisica'] === null) {
                    continue;
                }

                $inventario = Inventario::where('producto_id', $item['producto_id'])->first();
                if (!$inventario) {
                    continue;
                }

                $inventario->update(['cantidad' => $item['cantidad_fisica']]);
            }
        });

        return redirect()->route('cajas.index')
            ->with('success', 'Cierre de inventario guardado y stock actualizado correctamente.');
    }

    public function show(CierreInventario $cierre): View
    {
        return view('caja.cierre-inventario-ver', compact('cierre'));
    }
}
