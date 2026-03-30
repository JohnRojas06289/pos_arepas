<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Agrega la columna `revertida` a la tabla ventas.
 *
 * Se usa smallint en lugar de boolean por el mismo motivo que `pagado`:
 * Laravel's prepareBindings() convierte PHP bool → int (0/1) antes de enviarlo
 * a PDO. PostgreSQL estricto rechaza el entero en una columna boolean.
 * Con smallint, 0 = no revertida, 1 = revertida. El cast 'boolean' del modelo
 * Eloquent sigue funcionando correctamente.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE ventas ADD COLUMN revertida SMALLINT NOT NULL DEFAULT 0');
        } else {
            Schema::table('ventas', function (Blueprint $table) {
                $table->boolean('revertida')->default(false)->after('saldo_pendiente');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('revertida');
        });
    }
};
