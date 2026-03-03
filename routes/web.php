<?php

require __DIR__ . '/debug.php';

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\categoriaController;
use App\Http\Controllers\clienteController;
use App\Http\Controllers\compraController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\ExportExcelController;
use App\Http\Controllers\ExportPDFController;
use App\Http\Controllers\homeController;
use App\Http\Controllers\ImportExcelController;
use App\Http\Controllers\InventarioControlller;
use App\Http\Controllers\KardexController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\logoutController;
use App\Http\Controllers\marcaController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\presentacioneController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\proveedorController;
use App\Http\Controllers\roleController;
use App\Http\Controllers\userController;
use App\Http\Controllers\ventaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Ecommerce público eliminado — la app es un POS interno.
// La raíz redirige directamente al login.
Route::get('/', function () {
    return redirect()->route('login.index');
})->name('home');

Route::get('/migrate-db-secret-key-12345', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh --force');
        return 'Database migrated successfully! Output: ' . \Illuminate\Support\Facades\Artisan::output();
    } catch (\Exception $e) {
        return 'Error migrating: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString();
    }
});

Route::get('/debug-spatie', function () {
    try {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) return "Not logged in";
        $can = $user->can('ver-panel');
        return "Can ver-panel? " . ($can ? 'YES' : 'NO');
    } catch (\Exception $e) {
        return "Spatie Error: " . $e->getMessage();
    }
})->middleware('auth');

Route::get('/debug-query', function () {
    try {
        $fechaInicio = \Carbon\Carbon::now()->subDays(7)->format('Y-m-d');
        $fechaFin = \Carbon\Carbon::now()->format('Y-m-d');
        
        $results = \Illuminate\Support\Facades\DB::table('ventas')
            ->selectRaw('CAST(created_at AS DATE) as fecha, SUM(total) as total')
            ->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->groupBy(\Illuminate\Support\Facades\DB::raw('CAST(created_at AS DATE)'))
            ->orderBy('fecha', 'asc')
            ->get();
        return "Query OK. Count: " .Count($results);
    } catch (\Exception $e) {
        return "Query Error: " . $e->getMessage();
    }
})->middleware('auth');

Route::get('/debug-view-simple', function () {
    try {
        return view('debug-simple');
    } catch (\Exception $e) {
        return "View Error: " . $e->getMessage();
    }
})->middleware('auth');

Route::get('/debug-layout-data', function () {
    try {
        $data = [];
        
        // 1. Test Empresa Access
        try {
            $empresa = \App\Models\Empresa::first();
            $data['empresa'] = $empresa ? "Found: " . $empresa->nombre : "Not Found (Handled OK)";
        } catch (\Exception $e) {
            $data['empresa_error'] = $e->getMessage();
        }

        // 2. Test Notifications Access
        try {
            $user = \Illuminate\Support\Facades\Auth::user();
            $count = $user->unreadNotifications->count();
            $data['notifications'] = "Count: " . $count;
        } catch (\Exception $e) {
            $data['notifications_error'] = $e->getMessage();
        }

        return $data;

    } catch (\Exception $e) {
        return "General Error: " . $e->getMessage();
    }
})->middleware('auth');

Route::middleware('auth')->get('/panel', [homeController::class, 'index'])->name('panel');



Route::group(['middleware' => 'auth', 'prefix' => 'admin'], function () {
    Route::resource('presentaciones', presentacioneController::class)->except('show');
    Route::resource('marcas', marcaController::class)->except('show');
    Route::get('productos/export', [ProductoController::class, 'export'])->name('productos.export');
    Route::post('productos/import', [ProductoController::class, 'import'])->name('productos.import');
    Route::resource('productos', ProductoController::class)->except('show', 'destroy');
    Route::resource('clientes', clienteController::class)->except('show');
    Route::resource('proveedores', proveedorController::class)->except('show');
    Route::resource('compras', compraController::class)->except('edit', 'update', 'destroy');
    Route::resource('ventas', ventaController::class)->except('edit', 'update', 'destroy');
    Route::resource('users', userController::class)->except('show');
    Route::resource('roles', roleController::class)->except('show');
    Route::resource('profile', profileController::class)->only('index', 'update');
    Route::resource('activityLog', ActivityLogController::class)->only('index');
    Route::resource('inventario', InventarioControlller::class)->except('show');
    Route::resource('kardex', KardexController::class)->only('index');
    Route::resource('empresa', EmpresaController::class)->only('index', 'update');
    Route::resource('empleados', EmpleadoController::class)->except('show');
    Route::resource('cajas', CajaController::class)->except('edit', 'update', 'show');
    Route::resource('movimientos', MovimientoController::class)->except('show', 'edit', 'update', 'destroy');
    Route::resource('categorias', categoriaController::class)->except('show');


    //Reportes
    Route::get('/export-pdf-comprobante-venta/{id}', [ExportPDFController::class, 'exportPdfComprobanteVenta'])
        ->name('export.pdf-comprobante-venta');

    Route::get('/export-excel-vental-all', [ExportExcelController::class, 'exportExcelVentasAll'])
        ->name('export.excel-ventas-all');

    Route::post('/importar-excel-empleados', [ImportExcelController::class, 'importExcelEmpleados'])
        ->name('import.excel-empleados');

    Route::post('/notifications/mark-as-read', function () {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.markAsRead');

    Route::get('/logout', [logoutController::class, 'logout'])->name('logout');
});



Route::get('/login', [loginController::class, 'index'])->name('login.index');
Route::post('/login', [loginController::class, 'login'])->name('login.login');


