<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ForceAddPanelPermissionSeeder extends Seeder
{
    public function run()
    {
        // Force clear cache first
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->command->info('Cache cleared.');

        $p = Permission::firstOrCreate(['name' => 'ver-panel', 'guard_name' => 'web']);
        $this->command->info('Permission ver-panel ID: ' . $p->id);

        $role = Role::where('name', 'administrador')->first();
        if ($role) {
            $role->givePermissionTo($p);
            $this->command->info('Role administrator given permission.');
        } else {
            $this->command->error('Role administrator not found.');
        }
    }
}
