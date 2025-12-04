<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Persona;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el primer documento disponible
        $documento = Documento::first();
        
        if (!$documento) {
            throw new \Exception('No hay documentos en la base de datos. Ejecuta DocumentoSeeder primero.');
        }
        
        // Crear persona
        $persona = Persona::create([
            'razon_social' => 'Cliente General',
            'direccion' => 'Sin dirección',
            'tipo' => 'NATURAL',
            'documento_id' => $documento->id,
            'numero_documento' => '00000000',
            'estado' => 1
        ]);

        // Crear cliente
        Cliente::create([
            'persona_id' => $persona->id
        ]);
        
        echo "✅ Cliente general creado exitosamente\n";
    }
}
