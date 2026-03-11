<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cambia la columna `pagado` de boolean a smallint.
 *
 * Motivo: Laravel's prepareBindings() convierte PHP bool → int (0/1) antes de
 * enviarlo a PDO. PostgreSQL estricto rechaza el entero 0 en una columna boolean
 * con error "column is of type boolean but expression is of type integer".
 * Con smallint, los valores 0 y 1 se almacenan y leen correctamente, y el cast
 * 'boolean' del modelo Eloquent sigue funcionando (0→false, 1→true).
 *
 * SQLite ya almacena booleans como integers internamente, por lo que no requiere
 * ningún cambio de tipo en SQLite.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // 1. Quitar el DEFAULT boolean (true) que impide el ALTER TYPE
            DB::statement('ALTER TABLE ventas ALTER COLUMN pagado DROP DEFAULT');
            // 2. Cambiar el tipo a smallint con conversión explícita
            DB::statement('ALTER TABLE ventas ALTER COLUMN pagado TYPE SMALLINT USING pagado::int');
            // 3. Restaurar el DEFAULT como entero (1 = true)
            DB::statement('ALTER TABLE ventas ALTER COLUMN pagado SET DEFAULT 1');
        }
        // SQLite ya almacena booleans como 0/1 — no requiere cambio de esquema
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE ventas ALTER COLUMN pagado DROP DEFAULT');
            DB::statement('ALTER TABLE ventas ALTER COLUMN pagado TYPE BOOLEAN USING CASE WHEN pagado = 1 THEN TRUE ELSE FALSE END');
            DB::statement('ALTER TABLE ventas ALTER COLUMN pagado SET DEFAULT TRUE');
        }
    }
};
