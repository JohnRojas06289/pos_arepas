<?php

namespace App\Http\Controllers;

use App\Enums\CategoriaGastoEnum;
use App\Enums\MetodoPagoEnum;
use App\Models\Gasto;
use App\Services\ActivityLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GastoController extends Controller
{
    public function index(): View
    {
        $gastos = Gasto::where('user_id', Auth::id())
            ->orderByDesc('fecha')
            ->orderByDesc('created_at')
            ->get();

        $categorias      = CategoriaGastoEnum::cases();
        $optionsMetodoPago = MetodoPagoEnum::cases();

        // Totales resumen
        $hoy  = now()->toDateString();
        $mes  = now()->format('Y-m');

        $totalHoy = $gastos->filter(fn($g) => $g->fecha->toDateString() === $hoy)->sum('monto');
        $totalMes = $gastos->filter(fn($g) => $g->fecha->format('Y-m') === $mes)->sum('monto');
        $totalAll = $gastos->sum('monto');

        return view('gasto.index', compact(
            'gastos', 'categorias', 'optionsMetodoPago',
            'totalHoy', 'totalMes', 'totalAll'
        ));
    }

    public function create(): View
    {
        $categorias        = CategoriaGastoEnum::cases();
        $optionsMetodoPago = MetodoPagoEnum::cases();

        return view('gasto.create', compact('categorias', 'optionsMetodoPago'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'categoria'    => 'required|string',
            'descripcion'  => 'required|string|max:255',
            'monto'        => 'required|numeric|min:1',
            'fecha'        => 'required|date',
            'metodo_pago'  => 'nullable|string',
            'comprobante'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'notas'        => 'nullable|string|max:1000',
        ]);

        try {
            $comprobantePath = null;
            if ($request->hasFile('comprobante')) {
                $file = $request->file('comprobante');
                $name = uniqid() . '.' . $file->getClientOriginalExtension();
                $comprobantePath = $file->storeAs('gastos', $name, config('filesystems.default'));
            }

            $gasto = Gasto::create([
                'user_id'          => Auth::id(),
                'categoria'        => $request->categoria,
                'descripcion'      => $request->descripcion,
                'monto'            => $request->monto,
                'fecha'            => $request->fecha,
                'metodo_pago'      => $request->metodo_pago,
                'comprobante_path' => $comprobantePath,
                'notas'            => $request->notas,
            ]);

            ActivityLogService::log('Registro de gasto', 'Gastos', ['gasto_id' => $gasto->id, 'monto' => $gasto->monto]);

            return redirect()->route('gastos.index')->with('success', 'Gasto registrado correctamente');
        } catch (Throwable $e) {
            Log::error('Error al registrar gasto', ['error' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Ups, algo falló al guardar el gasto');
        }
    }

    public function destroy(Gasto $gasto): RedirectResponse
    {
        try {
            if ($gasto->comprobante_path) {
                Storage::delete($gasto->comprobante_path);
            }
            $gasto->delete();
            ActivityLogService::log('Eliminación de gasto', 'Gastos', ['gasto_id' => $gasto->id]);

            return redirect()->route('gastos.index')->with('success', 'Gasto eliminado');
        } catch (Throwable $e) {
            Log::error('Error al eliminar gasto', ['error' => $e->getMessage()]);
            return redirect()->route('gastos.index')->with('error', 'Ups, algo falló');
        }
    }
}
