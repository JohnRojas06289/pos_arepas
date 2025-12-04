<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Caja;
use App\Models\User;

$user = User::first();
$caja = Caja::where('user_id', $user->id)->where('estado', 1)->first();

if ($caja) {
    echo "✅ Ya hay una caja abierta para {$user->email}\n";
    echo "Caja ID: {$caja->id}\n";
    echo "Monto inicial: {$caja->monto_inicial}\n";
} else {
    echo "❌ No hay caja abierta para {$user->email}\n";
    echo "Creando caja...\n";
    
    $caja = Caja::create([
        'user_id' => $user->id,
        'monto_inicial' => 0,
        'estado' => 1
    ]);
    
    echo "✅ Caja creada: {$caja->id}\n";
}
