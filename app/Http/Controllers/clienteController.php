<?php

namespace App\Http\Controllers;

use App\Enums\TipoPersonaEnum;
use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Persona;
use App\Services\ActivityLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Enums\MetodoPagoEnum;
use App\Enums\TipoMovimientoEnum;
use App\Models\Caja;
use App\Models\Movimiento;
use App\Models\Venta;
use Illuminate\Http\Request; // Importación correcta de Request
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class clienteController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-cliente|crear-cliente|editar-cliente|eliminar-cliente', ['only' => ['index']]);
        $this->middleware('permission:crear-cliente', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-cliente', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-cliente', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $clientes = Cliente::with('persona.documento')
            ->whereHas('persona', function($query) {
                $query->where('estado', 1); // smallint column, not boolean
            })
            ->latest()
            ->get();
        return view('cliente.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $documentos = Documento::all();
        $optionsTipoPersona = TipoPersonaEnum::cases();
        return view('cliente.create', compact('documentos', 'optionsTipoPersona'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonaRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $persona = Persona::create($request->validated());
            
            // Validate and create Cliente with tipo_cliente
            $clienteData = ['tipo_cliente' => $request->input('tipo_cliente', 'general')];
            $persona->cliente()->create($clienteData);
            
            DB::commit();

            ActivityLogService::log('Creacion de cliente', 'Clientes', $request->validated());

            return redirect()->route('clientes.index')->with('success', 'Cliente registrado');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear al cliente', ['error' => $e->getMessage()]);
            return redirect()->route('clientes.index')->with('error', 'Ups, algo falló');
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
    public function edit(Cliente $cliente): View
    {
        $cliente->load('persona.documento');
        $documentos = Documento::all();
        return view('cliente.edit', compact('cliente', 'documentos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        try {
            $cliente->persona->update($request->validated());
            
            // Update tipo_cliente if present
            if ($request->has('tipo_cliente')) {
                $cliente->update(['tipo_cliente' => $request->input('tipo_cliente')]);
            }
            
            ActivityLogService::log('Edición de cliente', 'Clientes', $request->validated());

            return redirect()->route('clientes.index')->with('success', 'Cliente editado');
        } catch (Throwable $e) {
            Log::error('Error al editar al cliente', ['error' => $e->getMessage()]);
            return redirect()->route('clientes.index')->with('error', 'Ups, algo falló');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $cliente = Cliente::findOrfail($id);
            $persona = $cliente->persona;

            $nuevoEstado = $persona->estado == 1 ? 0 : 1;
            $persona->update(['estado' => $nuevoEstado]);
            $message = $nuevoEstado == 1 ? 'Cliente restaurado' : 'Cliente eliminado';

            ActivityLogService::log($message, 'Clientes', [
                'cliente_id' => $id,
                'persona_id' => $persona->id,
                'estado' => $nuevoEstado
            ]);

            return redirect()->route('clientes.index')->with('success', $message);
        } catch (Throwable $e) {
            Log::error('Error al eliminar/restaurar al cliente', ['error' => $e->getMessage()]);
            return redirect()->route('clientes.index')->with('error', 'Ups, algo falló');
        }
    }

    /**
     * Pagar deuda de cliente (Abono)
     */
    public function pagarDeuda(Request $request, Cliente $cliente)
    {
        $request->validate([
            'monto' => 'required|numeric|min:1',
            'metodo_pago' => ['required', new \Illuminate\Validation\Rules\Enum(MetodoPagoEnum::class)],
        ]);

        try {
            DB::beginTransaction();

            $montoAbono  = (float) $request->input('monto');
            $metodoPago  = $request->input('metodo_pago');

            // 1. Registrar Movimiento en Caja si hay caja abierta (opcional)
            $caja = Caja::where('user_id', Auth::id())->where('estado', 1)->first();
            if ($caja) {
                Movimiento::create([
                    'tipo'        => TipoMovimientoEnum::Ingreso,
                    'descripcion' => 'Abono deuda cliente: ' . $cliente->persona->razon_social,
                    'monto'       => $montoAbono,
                    'metodo_pago' => $metodoPago,
                    'caja_id'     => $caja->id,
                ]);
            }

            // 2. Distribuir el abono en las ventas pendientes (FIFO)
            $ventasPendientes = $cliente->ventas()
                ->whereRaw('"pagado" = false')
                ->orderBy('created_at', 'asc')
                ->get();

            $montoRestante = $montoAbono;

            foreach ($ventasPendientes as $venta) {
                if ($montoRestante <= 0) break;

                if ($venta->saldo_pendiente <= $montoRestante) {
                    // Paga toda esta venta
                    $montoRestante -= $venta->saldo_pendiente;
                    // Use DB::update to avoid PHP bool → integer cast issues with PostgreSQL
                    DB::table('ventas')->where('id', $venta->id)->update([
                        'saldo_pendiente' => 0,
                        'pagado'          => DB::raw('true'),
                    ]);
                } else {
                    // Pago parcial
                    $nuevoSaldo = $venta->saldo_pendiente - $montoRestante;
                    $montoRestante = 0;
                    DB::table('ventas')->where('id', $venta->id)->update([
                        'saldo_pendiente' => $nuevoSaldo,
                    ]);
                }
            }

            DB::commit();
            ActivityLogService::log('Pago de deuda', 'Clientes', ['cliente_id' => $cliente->id, 'monto' => $montoAbono]);

            return redirect()->back()->with('success', 'Abono de $' . number_format($montoAbono, 0, ',', '.') . ' registrado correctamente.');

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al registrar pago de deuda', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }
}
