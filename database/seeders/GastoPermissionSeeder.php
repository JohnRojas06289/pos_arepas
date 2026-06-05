<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GastoPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $verGasto      = Permission::firstOrCreate(['name' => 'ver-gasto',      'guard_name' => 'web']);
        $crearGasto    = Permission::firstOrCreate(['name' => 'crear-gasto',    'guard_name' => 'web']);
        $eliminarGasto = Permission::firstOrCreate(['name' => 'eliminar-gasto', 'guard_name' => 'web']);

        // Administrador recibe todos
        $admin = Role::where('name', 'administrador')->first();
        if ($admin) {
            $admin->givePermissionTo([$verGasto, $crearGasto, $eliminarGasto]);
            $this->command->info('Permisos de gastos asignados al rol administrador.');
        }

        // Ventas: solo ver e ingresar gastos (sin eliminar)
        $ventas = Role::where('name', 'ventas')->first();
        if ($ventas) {
            $ventas->givePermissionTo([$verGasto, $crearGasto]);
            $this->command->info('Permisos ver-gasto y crear-gasto asignados al rol ventas.');
        } else {
            $this->command->warn('Rol "ventas" no encontrado. Créalo desde la UI y vuelve a ejecutar este seeder.');
        }
    }
}
