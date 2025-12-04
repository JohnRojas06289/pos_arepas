<?php

// Script para regenerar permisos con UUIDs
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

try {
    DB::beginTransaction();
    
    echo "=== Regenerando Permisos con UUIDs ===\n\n";
    
    // 1. Obtener todos los permisos actuales
    $permisosActuales = Permission::all();
    $permisosData = [];
    
    foreach ($permisosActuales as $permiso) {
        $permisosData[] = [
            'name' => $permiso->name,
            'guard_name' => $permiso->guard_name
        ];
    }
    
    echo "Permisos encontrados: " . count($permisosData) . "\n";
    
    // 2. Eliminar todos los permisos y roles
    echo "Eliminando permisos y roles antiguos...\n";
    DB::table('role_has_permissions')->delete();
    DB::table('model_has_permissions')->delete();
    DB::table('model_has_roles')->delete();
    DB::table('roles')->delete();
    DB::table('permissions')->delete();
    
    // 3. Recrear permisos con UUIDs
    echo "Recreando permisos con UUIDs...\n";
    foreach ($permisosData as $data) {
        $permiso = new Permission();
        $permiso->id = Str::uuid()->toString();
        $permiso->name = $data['name'];
        $permiso->guard_name = $data['guard_name'];
        $permiso->save();
        echo "  ✓ {$data['name']}\n";
    }
    
    // 4. Recrear rol de administrador
    echo "\nRecreando rol de administrador...\n";
    $adminRole = new Role();
    $adminRole->id = Str::uuid()->toString();
    $adminRole->name = 'administrador';
    $adminRole->guard_name = 'web';
    $adminRole->save();
    
    // 5. Asignar todos los permisos al administrador
    $adminRole->syncPermissions(Permission::all());
    
    echo "  ✓ Rol administrador creado con todos los permisos\n";
    
    // 6. Asignar rol administrador al primer usuario
    $user = \App\Models\User::first();
    if ($user) {
        $user->syncRoles(['administrador']);
        echo "  ✓ Rol asignado a {$user->email}\n";
    }
    
    DB::commit();
    
    echo "\n✅ ¡Permisos regenerados exitosamente!\n";
    echo "Ahora puedes crear roles personalizados desde la interfaz.\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
