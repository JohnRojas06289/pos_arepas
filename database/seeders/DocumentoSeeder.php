<?php

namespace Database\Seeders;

use App\Models\Documento;
use Illuminate\Database\Seeder;

class DocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Documento::insert([
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'nombre' => 'DNI',
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'nombre' => 'Pasaporte',
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'nombre' => 'RUC',
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'nombre' => 'Carnet Extranjería',
            ],
        ]);
    }
}
