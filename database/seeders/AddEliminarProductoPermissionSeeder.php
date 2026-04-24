<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddEliminarProductoPermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'eliminar-producto', 'guard_name' => 'web']);

        $role = Role::where('name', 'administrador')->first();
        if ($role) {
            $role->givePermissionTo('eliminar-producto');
        }
    }
}
