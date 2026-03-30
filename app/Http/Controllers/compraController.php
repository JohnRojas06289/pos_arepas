<?php

namespace App\Http\Controllers;

use App\Enums\MetodoPagoEnum;
use App\Events\CreateCompraDetalleEvent;
use App\Http\Requests\StoreCompraRequest;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedore;
use App\Services\ActivityLogService;
use App\Services\ComprobanteService;
use App\Services\EmpresaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class compraController extends Controller
{
    protected EmpresaService $empresaService;

    public function __construct(EmpresaService $empresaService)
    {
        $this->middleware('permission:ver-compra|crear-compra|mostrar-compra|eliminar-compra', ['only' => ['index']]);
        $this->middleware('permission:crear-compra', ['only' => ['create', 'store']]);
        $this->middleware('permission:mostrar-compra', ['only' => ['show']]);
        $this->middleware('check-show-compra-user', ['only' => ['show']]);
        $this->empresaService = $empresaService;
    }

    public function index(): View
    {
        $compras = Compra::with('comprobante', 'proveedore.persona', 'productos')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('compra.index', compact('compras'));
    }

    public function create(ComprobanteService $comprobanteService): View
    {
        $proveedores = Proveedore::whereHas('persona', fn ($q) => $q->where('estado', 1))->get();
        $comprobantes      = $comprobanteService->obtenerComprobantes();
        $productos         = Producto::where('estado', 1)->get();
        $optionsMetodoPago = MetodoPagoEnum::cases();
        $empresa           = $this->empresaService->obtenerEmpresa();

        return view('compra.create', compact(
            'proveedores',
            'comprobantes',
            'productos',
            'optionsMetodoPago',
            'empresa'
        ));
    }

    public function store(StoreCompraRequest $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $compraModel = new Compra();

            $comprobantePath = $request->hasFile('file_comprobante')
                ? $compraModel->handleUploadFile($request->file('file_comprobante'))
                : null;

            $compra = Compra::create(array_merge($request->validated(), [
                'user_id'          => Auth::id(),
                'impuesto'         => 0,
                'comprobante_path' => $comprobantePath,
            ]));

            $productoIds        = $request->get('arrayidproducto', []);
            $cantidades         = $request->get('arraycantidad', []);
            $preciosCompra      = $request->get('arraypreciocompra', []);
            $fechasVencimiento  = $request->get('arrayfechavencimiento', []);
            $totalProductos     = count($productoIds);

            for ($i = 0; $i < $totalProductos; $i++) {
                $compra->productos()->syncWithoutDetaching([
                    $productoIds[$i] => [
                        'id'               => Str::uuid()->toString(),
                        'cantidad'         => $cantidades[$i],
                        'precio_compra'    => $preciosCompra[$i],
                        'fecha_vencimiento'=> $fechasVencimiento[$i],
                    ],
                ]);

                CreateCompraDetalleEvent::dispatch(
                    $compra,
                    $productoIds[$i],
                    $cantidades[$i],
                    $preciosCompra[$i],
                    $fechasVencimiento[$i]
                );
            }

            DB::commit();
            ActivityLogService::log('Creación de compra', 'Compras', array_merge($request->validated(), ['compra_id' => $compra->id]));

            return redirect()->route('compras.index')->with('success', 'Compra registrada con éxito');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear la compra', ['error' => $e->getMessage()]);
            return redirect()->route('compras.index')->with('error', 'Ups, algo falló');
        }
    }

    public function show(Compra $compra): View
    {
        $empresa = $this->empresaService->obtenerEmpresa();
        return view('compra.show', compact('compra', 'empresa'));
    }
}
