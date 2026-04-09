<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AgenteIAController;
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
| Aplicación POS interno — la raíz redirige al login.
|
*/

Route::get('/', function () {
    return redirect()->route('login.index');
})->name('home');

Route::middleware('auth')->get('/panel', [homeController::class, 'index'])->name('panel');

Route::group(['middleware' => 'auth', 'prefix' => 'admin'], function () {

    // ── Catálogo ──────────────────────────────────────────────────────────
    Route::resource('presentaciones', presentacioneController::class)->except('show');
    Route::resource('marcas', marcaController::class)->except('show');
    Route::resource('categorias', categoriaController::class)->except('show');

    // ── Productos ─────────────────────────────────────────────────────────
    Route::get('productos/export', [ProductoController::class, 'export'])->name('productos.export');
    Route::post('productos/import', [ProductoController::class, 'import'])->name('productos.import');
    Route::resource('productos', ProductoController::class)->except('show', 'destroy');

    // ── Inventario ────────────────────────────────────────────────────────
    Route::get('inventario/ventas-detalle/{producto}', [InventarioControlller::class, 'ventasDetalle'])
        ->name('inventario.ventas-detalle');
    Route::get('inventario/compras-detalle/{producto}', [InventarioControlller::class, 'comprasDetalle'])
        ->name('inventario.compras-detalle');
    Route::resource('inventario', InventarioControlller::class)->except('show');
    Route::resource('kardex', KardexController::class)->only('index');

    // ── Personas ──────────────────────────────────────────────────────────
    Route::resource('clientes', clienteController::class)->except('show');
    Route::post('clientes/{cliente}/pagar-deuda', [clienteController::class, 'pagarDeuda'])
        ->name('clientes.pagarDeuda');
    Route::resource('proveedores', proveedorController::class)->except('show');
    Route::resource('empleados', EmpleadoController::class)->except('show');

    // ── Ventas y Compras ──────────────────────────────────────────────────
    Route::get('/pos', [ventaController::class, 'create'])->name('pos.index');
    Route::resource('ventas', ventaController::class)->except('edit', 'update', 'destroy');
    Route::post('compras/scan-factura', [compraController::class, 'scanFactura'])->name('compras.scan-factura');
    Route::resource('compras', compraController::class)->except('edit', 'update', 'destroy');
    Route::resource('cajas', CajaController::class)->except('edit', 'update', 'show');
    Route::get('cajas/{caja}/resumen', [CajaController::class, 'resumen'])->name('cajas.resumen');
    Route::resource('movimientos', MovimientoController::class)->except('show', 'edit', 'update', 'destroy');

    // ── Administración ────────────────────────────────────────────────────
    Route::get('/estadisticas', [homeController::class, 'estadisticas'])->name('admin.estadisticas');
    Route::resource('users', userController::class)->except('show');
    Route::resource('roles', roleController::class)->except('show');
    Route::resource('profile', profileController::class)->only('index', 'update');
    Route::resource('empresa', EmpresaController::class)->only('index', 'update');
    Route::resource('activityLog', ActivityLogController::class)->except(['create', 'store', 'edit', 'update']);
    Route::post('activityLog/{logId}/reverse-venta', [ActivityLogController::class, 'reverseVenta'])->name('activityLog.reverseVenta');
    Route::put('activityLog/{logId}/update-venta', [ActivityLogController::class, 'updateVenta'])->name('activityLog.updateVenta');

    // ── Reportes ──────────────────────────────────────────────────────────
    Route::get('/export-pdf-comprobante-venta/{id}', [ExportPDFController::class, 'exportPdfComprobanteVenta'])
        ->name('export.pdf-comprobante-venta');
    Route::get('/export-excel-ventas-all', [ExportExcelController::class, 'exportExcelVentasAll'])
        ->name('export.excel-ventas-all');
    Route::post('/importar-excel-empleados', [ImportExcelController::class, 'importExcelEmpleados'])
        ->name('import.excel-empleados');

    // ── Utilidades ────────────────────────────────────────────────────────
    Route::post('/notifications/mark-as-read', function () {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.markAsRead');

    Route::get('/logout', [logoutController::class, 'logout'])->name('logout');

    // ── Agente IA ─────────────────────────────────────────────────────────
    Route::post('/agente-ia/chat', [AgenteIAController::class, 'chat'])->name('agente-ia.chat');
});

Route::get('/login', [loginController::class, 'index'])->name('login.index');
Route::post('/login', [loginController::class, 'login'])->name('login.login');
