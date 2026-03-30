<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Convierte la columna `revertida` de boolean a smallint.
 *
 * Mismo motivo que `pagado`: Laravel convierte PHP bool → int (0/1) antes de
 * enviarlo a PDO. PostgreSQL rechaza el entero en una columna boolean.
 * Con smallint: 0 = no revertida, 1 = revertida.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE ventas ALTER COLUMN revertida DROP DEFAULT');
            DB::statement('ALTER TABLE ventas ALTER COLUMN revertida TYPE SMALLINT USING revertida::int');
            DB::statement('ALTER TABLE ventas ALTER COLUMN revertida SET DEFAULT 0');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE ventas ALTER COLUMN revertida DROP DEFAULT');
            DB::statement('ALTER TABLE ventas ALTER COLUMN revertida TYPE BOOLEAN USING CASE WHEN revertida = 1 THEN TRUE ELSE FALSE END');
            DB::statement('ALTER TABLE ventas ALTER COLUMN revertida SET DEFAULT FALSE');
        }
    }
};
