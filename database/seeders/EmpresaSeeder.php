<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moneda = \App\Models\Moneda::where('estandar_iso', 'COP')->first();
        Empresa::insert([
            'id' => \Illuminate\Support\Str::uuid(),
            'nombre' => 'Arepas',
            'propietario' => 'Jairo Rojas',
            'ruc' => '11111111',
            'direccion' => 'CC Lo Nuestro',
            'moneda_id' => $moneda->id
        ]);
    }
}
