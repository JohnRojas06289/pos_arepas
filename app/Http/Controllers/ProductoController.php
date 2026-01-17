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
    protected $productoService;

    function __construct(ProductoService $productoService)
    {
        $this->productoService = $productoService;
        $this->middleware('permission:ver-producto|crear-producto|editar-producto|eliminar-producto', ['only' => ['index']]);
        $this->middleware('permission:crear-producto', ['only' => ['create', 'store', 'import']]);
        $this->middleware('permission:editar-producto', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-producto', ['only' => ['destroy']]);
        $this->middleware('permission:ver-producto', ['only' => ['export']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $productos = Producto::with([
            'categoria.caracteristica',
            'marca.caracteristica',
            'presentacione.caracteristica'
        ])
            ->orderByRaw('CAST(codigo AS BIGINT) ASC')
            ->get();

        return view('producto.index', compact('productos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $marcas = Marca::join('caracteristicas as c', 'marcas.caracteristica_id', '=', 'c.id')
            ->select('marcas.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();

        $presentaciones = Presentacione::join('caracteristicas as c', 'presentaciones.caracteristica_id', '=', 'c.id')
            ->select('presentaciones.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();

        $categorias = Categoria::join('caracteristicas as c', 'categorias.caracteristica_id', '=', 'c.id')
            ->select('categorias.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();

        // Get the last product code and suggest the next one (numeric sorting)
        $ultimoProducto = Producto::orderByRaw('CAST(codigo AS BIGINT) DESC')->first();
        $codigoSugerido = $ultimoProducto && $ultimoProducto->codigo ? (string)((int)$ultimoProducto->codigo + 1) : '1';

        return view('producto.create', compact('marcas', 'presentaciones', 'categorias', 'codigoSugerido'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductoRequest $request): RedirectResponse
    {
        try {
            $this->productoService->crearProducto($request->validated());
            ActivityLogService::log('Creación de producto', 'Productos', $request->validated());
            return redirect()->route('productos.index')->with('success', 'Producto registrado');
        } catch (Throwable $e) {
            Log::error('Error al crear el producto', ['error' => $e->getMessage()]);
            return redirect()->route('productos.index')->with('error', 'Error: ' . $e->getMessage());
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
    public function edit(Producto $producto): View
    {
        $marcas = Marca::join('caracteristicas as c', 'marcas.caracteristica_id', '=', 'c.id')
            ->select('marcas.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();

        $presentaciones = Presentacione::join('caracteristicas as c', 'presentaciones.caracteristica_id', '=', 'c.id')
            ->select('presentaciones.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();

        $categorias = Categoria::join('caracteristicas as c', 'categorias.caracteristica_id', '=', 'c.id')
            ->select('categorias.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();

        return view('producto.edit', compact('producto', 'marcas', 'presentaciones', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductoRequest $request, Producto $producto): RedirectResponse
    {
        try {
            \Illuminate\Support\Facades\Log::info('Producto update request', [
                'has_file' => $request->hasFile('img_path'),
                'file_valid' => $request->hasFile('img_path') ? $request->file('img_path')->isValid() : false,
                'file_error' => $request->hasFile('img_path') ? $request->file('img_path')->getError() : 'N/A',
                'file_size' => $request->hasFile('img_path') ? $request->file('img_path')->getSize() : 'N/A',
                'post_data' => $request->except(['img_path']),
            ]);

            $this->productoService->editarProducto($request->validated(), $producto);
            ActivityLogService::log('Edición de producto', 'Productos', $request->validated());
            return redirect()->route('productos.index')->with('success', 'Producto editado');
        } catch (Throwable $e) {
            Log::error('Error al editar el producto', ['error' => $e->getMessage()]);
            return redirect()->route('productos.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Export products to CSV/Excel file
     */
    public function export()
    {
        try {
            // Get all products with relationships
            $productos = Producto::with([
                'categoria.caracteristica',
                'marca.caracteristica',
                'presentacione.caracteristica',
                'inventario'
            ])->get();

            // Create filename with current date
            $filename = 'productos_' . date('Y-m-d') . '.csv';

            // Set headers for CSV download
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            // Create callback for streaming CSV
            $callback = function() use ($productos) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for UTF-8 (helps Excel recognize special characters)
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Add CSV headers
                fputcsv($file, [
                    'Código',
                    'Nombre',
                    'Descripción',
                    'Precio',
                    'Categoría',
                    'Marca',
                    'Presentación',
                    'Stock',
                    'Estado'
                ]);

                // Add product data
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
                        $producto->estado ? 'Activo' : 'Inactivo'
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
     * Import products from CSV file
     */
    public function import(Request $request): RedirectResponse
    {
        try {
            // Validate file upload
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:2048'
            ]);

            $file = $request->file('file');
            $path = $file->getRealPath();
            
            // Open and read CSV
            $csv = fopen($path, 'r');
            
            // Skip BOM if present
            $bom = fread($csv, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($csv);
            }
            
            // Read header
            $header = fgetcsv($csv);
            
            if (!$header) {
                return redirect()->route('productos.index')->with('error', 'El archivo CSV está vacío o no tiene el formato correcto.');
            }

            $created = 0;
            $failed = 0;
            $errors = [];

            // Process each row
            while (($row = fgetcsv($csv)) !== false) {
                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Map CSV columns to data
                    $data = array_combine($header, $row);
                    
                    // Validate required fields
                    if (empty($data['Nombre'])) {
                        throw new \Exception('Nombre es requerido');
                    }

                    // Find or create relationships
                    $categoria = null;
                    if (!empty($data['Categoría']) && $data['Categoría'] !== 'Sin categoría') {
                        $categoriaCaract = Caracteristica::where('nombre', $data['Categoría'])->first();
                        if ($categoriaCaract) {
                            $categoria = Categoria::where('caracteristica_id', $categoriaCaract->id)->first();
                        }
                    }

                    $marca = null;
                    if (!empty($data['Marca']) && $data['Marca'] !== 'Sin marca') {
                        $marcaCaract = Caracteristica::where('nombre', $data['Marca'])->first();
                        if ($marcaCaract) {
                            $marca = Marca::where('caracteristica_id', $marcaCaract->id)->first();
                        }
                    }

                    $presentacion = null;
                    if (!empty($data['Presentación']) && $data['Presentación'] !== 'Sin presentación') {
                        $presentacionCaract = Caracteristica::where('nombre', $data['Presentación'])->first();
                        if ($presentacionCaract) {
                            $presentacion = Presentacione::where('caracteristica_id', $presentacionCaract->id)->first();
                        }
                    }

                    // Create product
                    $producto = Producto::create([
                        'codigo' => !empty($data['Código']) ? $data['Código'] : null,
                        'nombre' => $data['Nombre'],
                        'descripcion' => $data['Descripción'] ?? null,
                        'precio' => !empty($data['Precio']) ? (float)$data['Precio'] : 0,
                        'categoria_id' => $categoria?->id,
                        'marca_id' => $marca?->id,
                        'presentacione_id' => $presentacion?->id,
                        'estado' => (isset($data['Estado']) && $data['Estado'] === 'Activo') ? 1 : 0
                    ]);

                    // Create inventory record if stock is provided
                    if (isset($data['Stock']) && is_numeric($data['Stock'])) {
                        $producto->inventario()->create([
                            'stock' => (int)$data['Stock']
                        ]);
                    }

                    $created++;

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Fila " . ($created + $failed + 1) . ": " . $e->getMessage();
                }
            }

            fclose($csv);

            ActivityLogService::log('Importación de productos', 'Productos', [
                'creados' => $created,
                'fallidos' => $failed
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        /*
        $message = '';
        $producto = Producto::find($id);
        if ($producto->estado == 1) {
            Producto::where('id', $producto->id)
                ->update([
                    'estado' => 0
                ]);
            $message = 'Producto eliminado';
        } else {
            Producto::where('id', $producto->id)
                ->update([
                    'estado' => 1
                ]);
            $message = 'Producto restaurado';
        }

        return redirect()->route('productos.index')->with('success', $message);*/
    }
}
