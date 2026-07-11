<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AgenteIAController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\categoriaController;
use App\Http\Controllers\clienteController;
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
use App\Http\Controllers\CierreInventarioController;
use App\Http\Controllers\CarritoPOSController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PublicController;
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
    Route::resource('productos', ProductoController::class)->except('show');

    // ── Inventario ────────────────────────────────────────────────────────
    Route::get('inventario/ventas-detalle/{producto}', [InventarioControlller::class, 'ventasDetalle'])
        ->name('inventario.ventas-detalle');
    Route::get('inventario/compras-detalle/{producto}', [InventarioControlller::class, 'comprasDetalle'])
        ->name('inventario.compras-detalle');
    Route::post('inventario/ajustar-stock', [InventarioControlller::class, 'ajustarStock'])->name('inventario.ajustar-stock');
    Route::resource('inventario', InventarioControlller::class)->except('show');
    Route::post('inventario/sincronizar-kardex', [InventarioControlller::class, 'sincronizarKardex'])
        ->name('inventario.sincronizar-kardex');
    Route::resource('kardex', KardexController::class)->only('index');

    // ── Personas ──────────────────────────────────────────────────────────
    Route::resource('clientes', clienteController::class)->except('show');
    Route::post('clientes/{cliente}/pagar-deuda', [clienteController::class, 'pagarDeuda'])
        ->name('clientes.pagarDeuda');
    Route::resource('proveedores', proveedorController::class)->except('show');
    Route::resource('empleados', EmpleadoController::class)->except('show');

    // ─�� Gastos ────────────────────────────────────────────────────────────
    Route::post('gastos/scan-factura', [GastoController::class, 'scanFactura'])->name('gastos.scan-factura');
    Route::resource('gastos', GastoController::class)->only('index', 'create', 'store', 'destroy');

    // ── Ventas ────────────────────────────────────────────────────────────
    Route::get('/pos', [ventaController::class, 'create'])->name('pos.index');
    Route::resource('ventas', ventaController::class)->except('edit', 'update', 'destroy');

    // ── Carritos POS (persistencia en BD) ─────────────────────────────────
    Route::get('/pos/carritos',          [CarritoPOSController::class, 'index'])->name('pos.carritos.index');
    Route::post('/pos/carritos/sync',    [CarritoPOSController::class, 'sync'])->name('pos.carritos.sync');
    Route::delete('/pos/carritos/{uuid}',[CarritoPOSController::class, 'destroy'])->name('pos.carritos.destroy');
    Route::resource('cajas', CajaController::class)->except('edit', 'update', 'show');
    Route::get('cajas/{caja}/resumen', [CajaController::class, 'resumen'])->name('cajas.resumen');
    Route::get('cajas/{caja}/cierre-inventario',  [CierreInventarioController::class, 'create'])->name('cajas.cierre-inventario.create');
    Route::post('cajas/{caja}/cierre-inventario', [CierreInventarioController::class, 'store'])->name('cajas.cierre-inventario.store');
    Route::get('cierre-inventario/{cierre}',      [CierreInventarioController::class, 'show'])->name('cierre-inventario.show');
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

    // ── Pedidos ───────────────────────────────────────────────────────────
    Route::get('/pedidos/panel',      [PedidoController::class, 'panel'])->name('pedidos.panel');
    Route::get('/pedido',             [PedidoController::class, 'create'])->name('pedidos.create');
    Route::post('/pedido',            [PedidoController::class, 'store'])->name('pedidos.store');
    Route::get('/pedidos/pendientes', [PedidoController::class, 'pendientes'])->name('pedidos.pendientes');
    Route::post('/pedidos/{pedido}/tomar', [PedidoController::class, 'tomar'])->name('pedidos.tomar');

    // ── Agente IA ─────────────────────────────────────────────────────────
    Route::post('/agente-ia/chat', [AgenteIAController::class, 'chat'])->name('agente-ia.chat');

    // ── Catálogo Público ──────────────────────────────────────────────────
    Route::post('productos/{producto}/toggle-catalogo', [ProductoController::class, 'toggleCatalogo'])
        ->name('productos.toggle-catalogo');
});

// ── Sitio Público ─────────────────────────────────────────────────────────
Route::get('/catalogo', [PublicController::class, 'collection'])->name('collection');
Route::get('/nosotros', [PublicController::class, 'about'])->name('about');
Route::get('/contacto', [PublicController::class, 'contact'])->name('contact');

Route::get('/login', [loginController::class, 'index'])->name('login.index');
Route::post('/login', [loginController::class, 'login'])->name('login.login');
