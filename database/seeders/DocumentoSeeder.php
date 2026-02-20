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
        $documentos = [
            'Cédula de ciudadania',
            'Pasaporte',
            'NIT',
            'Cédula de extranjeria'
        ];

        foreach ($documentos as $nombre) {
            Documento::create([
                'nombre' => $nombre
            ]);
        }
    }
}
