<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('monedas')->insertOrIgnore([
            'id' => Str::uuid(),
            'estandar_iso' => 'COP',
            'nombre_completo' => 'Peso Colombiano',
            'simbolo' => '$',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('monedas')->where('estandar_iso', 'COP')->delete();
    }
};
