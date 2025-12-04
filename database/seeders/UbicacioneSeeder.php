<?php

namespace Database\Seeders;

use App\Models\Ubicacione;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UbicacioneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ubicacione::insert([
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'nombre' => 'Estante 1',
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'nombre' => 'Estante 2',
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'nombre' => 'Estante 3',
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'nombre' => 'Estante 4',
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'nombre' => 'Estante 5',
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'nombre' => 'Estante 6',
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'nombre' => 'Estante 7',
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'nombre' => 'Estante 8',
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'nombre' => 'Estante 9',
            ],
        ]);
    }
}
