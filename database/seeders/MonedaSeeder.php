<?php

namespace Database\Seeders;

use App\Models\Moneda;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MonedaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Moneda::insert([
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'estandar_iso' => 'USD',
                'nombre_completo' => 'Dólar estadounidense',
                'simbolo' => '$'
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'estandar_iso' => 'EUR',
                'nombre_completo' => 'Euro',
                'simbolo' => '€'
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'estandar_iso' => 'MXN',
                'nombre_completo' => 'Peso mexicano',
                'simbolo' => '$'
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'estandar_iso' => 'PEN',
                'nombre_completo' => 'Sol peruano',
                'simbolo' => 'S/'
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'estandar_iso' => 'ARS',
                'nombre_completo' => 'Peso Argentino',
                'simbolo' => '$'
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'estandar_iso' => 'CLP',
                'nombre_completo' => 'Peso Chileno',
                'simbolo' => '$'
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'estandar_iso' => 'COP',
                'nombre_completo' => 'Peso Colombiano',
                'simbolo' => '$'
            ],
        ]);
    }
}
