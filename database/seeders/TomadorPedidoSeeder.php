<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TomadorPedidoSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurar que el permiso exista
        $permCrearPedido = Permission::firstOrCreate(['name' => 'crear-pedido', 'guard_name' => 'web']);
        $permVerPanel    = Permission::firstOrCreate(['name' => 'ver-panel',    'guard_name' => 'web']);

        $tomadores = [
            ['role' => 'tomador-1', 'name' => 'Tomador 1', 'email' => 'tomador1@pos.com'],
            ['role' => 'tomador-2', 'name' => 'Tomador 2', 'email' => 'tomador2@pos.com'],
            ['role' => 'tomador-3', 'name' => 'Tomador 3', 'email' => 'tomador3@pos.com'],
        ];

        foreach ($tomadores as $data) {
            // Crear o recuperar el rol
            $role = Role::firstOrCreate(['name' => $data['role'], 'guard_name' => 'web']);
            $role->syncPermissions([$permVerPanel, $permCrearPedido]);

            // Crear o recuperar el usuario (HasUuids genera el UUID automáticamente)
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make('tomador123'),
                ]
            );

            $user->syncRoles([$role]);
        }
    }
}
