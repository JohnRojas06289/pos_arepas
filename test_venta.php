<?php

// Script de prueba para crear una venta manualmente
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\User;
use App\Models\Caja;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();
    
    $user = User::first();
    $cliente = Cliente::first();
    $comprobante = Comprobante::first();
    $caja = Caja::where('estado', 1)->first();
    
    echo "Usuario: {$user->email}\n";
    echo "Cliente: {$cliente->id}\n";
    echo "Comprobante: {$comprobante->nombre}\n";
    echo "Caja: {$caja->id} (Estado: {$caja->estado})\n\n";
    
    $venta = new Venta();
    $venta->cliente_id = $cliente->id;
    $venta->comprobante_id = $comprobante->id;
    $venta->metodo_pago = 'EFECTIVO';
    $venta->subtotal = 1000;
    $venta->total = 1000;
    $venta->monto_recibido = 1000;
    $venta->vuelto_entregado = 0;
    
    echo "Intentando guardar venta...\n";
    $venta->save();
    
    echo "✅ Venta creada exitosamente!\n";
    echo "ID: {$venta->id}\n";
    echo "Número: {$venta->numero_comprobante}\n";
    
    DB::commit();
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
