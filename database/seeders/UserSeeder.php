<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = \Illuminate\Support\Str::uuid()->toString();

        \Illuminate\Support\Facades\DB::table('users')->insert([
            'id' => $userId,
            'name' => 'Sak Noel',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('12345678'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        //Usuario administrador - crear rol sin especificar ID (auto-increment)
        $roleId = \Illuminate\Support\Facades\DB::table('roles')->insertGetId([
            'name' => 'administrador',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $permisos = \Illuminate\Support\Facades\DB::table('permissions')->get();
        
        foreach($permisos as $permiso) {
            \Illuminate\Support\Facades\DB::table('role_has_permissions')->insert([
                'permission_id' => $permiso->id,
                'role_id' => $roleId
            ]);
        }
        
        // Manual assignment to avoid foreign key issues with UUIDs in SQLite
        \Illuminate\Support\Facades\DB::table('model_has_roles')->insert([
            'role_id' => $roleId,
            'model_type' => \App\Models\User::class,
            'model_id' => $userId
        ]);
    }
}
