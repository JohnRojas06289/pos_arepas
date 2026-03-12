<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Caracteristica;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Presentacione;
use App\Models\Producto;
use App\Services\ActivityLogService;
use App\Services\ProductoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProductoController extends Controller
{
    protected ProductoService $productoService;

    public function __construct(ProductoService $productoService)
    {
        $this->productoService = $productoService;
        $this->middleware('permission:ver-producto|crear-producto|editar-producto|eliminar-producto', ['only' => ['index']]);
        $this->middleware('permission:crear-producto', ['only' => ['create', 'store', 'import']]);
        $this->middleware('permission:editar-producto', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-producto', ['only' => ['destroy']]);
        $this->middleware('permission:ver-producto', ['only' => ['export']]);
    }

    public function index(): View
    {
        $productos = Producto::with([
            'categoria.caracteristica',
            'marca.caracteristica',
            'presentacione.caracteristica',
        ])
            ->orderByRaw('LENGTH(codigo) ASC, codigo ASC')
            ->get();

        return view('producto.index', compact('productos'));
    }

    public function create(): View
    {
        [$marcas, $presentaciones, $categorias] = $this->getFormSelectOptions();

        $ultimoProducto  = Producto::orderByRaw('LENGTH(codigo) DESC, codigo DESC')->first();
        $codigoSugerido  = $ultimoProducto && $ultimoProducto->codigo
            ? (string) ((int) $ultimoProducto->codigo + 1)
            : '1';

        return view('producto.create', compact('marcas', 'presentaciones', 'categorias', 'codigoSugerido'));
    }

    public function store(StoreProductoRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();

            if (Producto::where('nombre', $data['nombre'])->exists()) {
                return redirect()->back()->withErrors(['nombre' => 'El nombre del producto ya existe.'])->withInput();
            }
            if (!empty($data['codigo']) && Producto::where('codigo', $data['codigo'])->exists()) {
                return redirect()->back()->withErrors(['codigo' => 'El código del producto ya existe.'])->withInput();
            }

            $this->productoService->crearProducto($data);
            ActivityLogService::log('Creación de producto', 'Productos', $data);

            return redirect()->route('productos.index')->with('success', 'Producto registrado');
        } catch (Throwable $e) {
            Log::error('Error al crear el producto', ['error' => $e->getMessage()]);
            return redirect()->route('productos.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit(Producto $producto): View
    {
        [$marcas, $presentaciones, $categorias] = $this->getFormSelectOptions();

        return view('producto.edit', compact('producto', 'marcas', 'presentaciones', 'categorias'));
    }

    public function update(UpdateProductoRequest $request, Producto $producto): RedirectResponse
    {
        try {
            $data = $request->validated();

            if (Producto::where('nombre', $data['nombre'])->where('id', '!=', $producto->id)->exists()) {
                return redirect()->back()->withErrors(['nombre' => 'El nombre ya existe.'])->withInput();
            }
            if (!empty($data['codigo']) && Producto::where('codigo', $data['codigo'])->where('id', '!=', $producto->id)->exists()) {
                return redirect()->back()->withErrors(['codigo' => 'El código ya existe.'])->withInput();
            }
            if (!empty($data['marca_id']) && !Marca::where('id', $data['marca_id'])->exists()) {
                return redirect()->back()->withErrors(['marca_id' => 'La marca seleccionada no existe.'])->withInput();
            }
            if (!empty($data['categoria_id']) && !Categoria::where('id', $data['categoria_id'])->exists()) {
                return redirect()->back()->withErrors(['categoria_id' => 'La categoría seleccionada no existe.'])->withInput();
            }
            if (!empty($data['presentacione_id']) && !Presentacione::where('id', $data['presentacione_id'])->exists()) {
                return redirect()->back()->withErrors(['presentacione_id' => 'La presentación seleccionada no existe.'])->withInput();
            }

            $this->productoService->editarProducto($data, $producto);
            ActivityLogService::log('Edición de producto', 'Productos', $data);

            return redirect()->route('productos.index')->with('success', 'Producto editado');
        } catch (Throwable $e) {
            Log::error('Error al editar el producto', ['error' => $e->getMessage()]);
            return redirect()->route('productos.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Exportar productos a CSV.
     */
    public function export(): mixed
    {
        try {
            $productos = Producto::with([
                'categoria.caracteristica',
                'marca.caracteristica',
                'presentacione.caracteristica',
                'inventario',
            ])->get();

            $filename = 'productos_' . date('Y-m-d') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            $callback = function () use ($productos) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($file, ['Código', 'Nombre', 'Descripción', 'Precio', 'Categoría', 'Marca', 'Presentación', 'Stock', 'Estado']);

                foreach ($productos as $producto) {
                    fputcsv($file, [
                        $producto->codigo,
                        $producto->nombre,
                        $producto->descripcion ?? 'Sin descripción',
                        number_format($producto->precio ?? 0, 2, '.', ''),
                        $producto->categoria->caracteristica->nombre ?? 'Sin categoría',
                        $producto->marca->caracteristica->nombre ?? 'Sin marca',
                        $producto->presentacione->caracteristica->nombre ?? 'Sin presentación',
                        $producto->inventario->stock ?? 0,
                        $producto->estado ? 'Activo' : 'Inactivo',
                    ]);
                }

                fclose($file);
            };

            ActivityLogService::log('Exportación de productos', 'Productos', ['total' => $productos->count()]);

            return response()->stream($callback, 200, $headers);
        } catch (Throwable $e) {
            Log::error('Error al exportar productos', ['error' => $e->getMessage()]);
            return redirect()->route('productos.index')->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    /**
     * Importar productos desde CSV.
     */
    public function import(Request $request): RedirectResponse
    {
        try {
            $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

            $path = $request->file('file')->getRealPath();
            $csv  = fopen($path, 'r');

            $bom = fread($csv, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($csv);
            }

            $header = fgetcsv($csv);
            if (!$header) {
                return redirect()->route('productos.index')->with('error', 'El archivo CSV está vacío o no tiene el formato correcto.');
            }

            $created = 0;
            $failed  = 0;
            $errors  = [];

            while (($row = fgetcsv($csv)) !== false) {
                try {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $data = array_combine($header, $row);

                    if (empty($data['Nombre'])) {
                        throw new \Exception('Nombre es requerido');
                    }

                    $categoria = null;
                    if (!empty($data['Categoría']) && $data['Categoría'] !== 'Sin categoría') {
                        $caract = Caracteristica::where('nombre', $data['Categoría'])->first();
                        if ($caract) {
                            $categoria = Categoria::where('caracteristica_id', $caract->id)->first();
                        }
                    }

                    $marca = null;
                    if (!empty($data['Marca']) && $data['Marca'] !== 'Sin marca') {
                        $caract = Caracteristica::where('nombre', $data['Marca'])->first();
                        if ($caract) {
                            $marca = Marca::where('caracteristica_id', $caract->id)->first();
                        }
                    }

                    $presentacion = null;
                    if (!empty($data['Presentación']) && $data['Presentación'] !== 'Sin presentación') {
                        $caract = Caracteristica::where('nombre', $data['Presentación'])->first();
                        if ($caract) {
                            $presentacion = Presentacione::where('caracteristica_id', $caract->id)->first();
                        }
                    }

                    $producto = Producto::create([
                        'codigo'           => !empty($data['Código']) ? $data['Código'] : null,
                        'nombre'           => $data['Nombre'],
                        'descripcion'      => $data['Descripción'] ?? null,
                        'precio'           => !empty($data['Precio']) ? (float) $data['Precio'] : 0,
                        'categoria_id'     => $categoria?->id,
                        'marca_id'         => $marca?->id,
                        'presentacione_id' => $presentacion?->id,
                        'estado'           => (isset($data['Estado']) && $data['Estado'] === 'Activo') ? 1 : 0,
                    ]);

                    if (isset($data['Stock']) && is_numeric($data['Stock'])) {
                        $producto->inventario()->create(['stock' => (int) $data['Stock']]);
                    }

                    $created++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = 'Fila ' . ($created + $failed + 1) . ': ' . $e->getMessage();
                }
            }

            fclose($csv);

            ActivityLogService::log('Importación de productos', 'Productos', [
                'creados'  => $created,
                'fallidos' => $failed,
            ]);

            $message = "Importación completada: {$created} productos creados";
            if ($failed > 0) {
                $message .= ", {$failed} fallidos. Errores: " . implode('; ', array_slice($errors, 0, 5));
            }

            return redirect()->route('productos.index')->with('success', $message);
        } catch (Throwable $e) {
            Log::error('Error al importar productos', ['error' => $e->getMessage()]);
            return redirect()->route('productos.index')->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }

    // ── Helpers privados ──────────────────────────────────────────────────

    /**
     * Devuelve [marcas, presentaciones, categorias] para los selects del formulario.
     */
    private function getFormSelectOptions(): array
    {
        $marcas = Marca::join('caracteristicas as c', 'marcas.caracteristica_id', '=', 'c.id')
            ->select('marcas.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->orderBy('c.nombre', 'asc')
            ->get();

        $presentaciones = Presentacione::join('caracteristicas as c', 'presentaciones.caracteristica_id', '=', 'c.id')
            ->select('presentaciones.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->orderBy('c.nombre', 'asc')
            ->get();

        $categorias = Categoria::join('caracteristicas as c', 'categorias.caracteristica_id', '=', 'c.id')
            ->select('categorias.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->orderBy('c.nombre', 'asc')
            ->get();

        return [$marcas, $presentaciones, $categorias];
    }
}
