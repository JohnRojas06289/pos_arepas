<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Agrega el valor 'REVERSA' al CHECK constraint de kardex.tipo_transaccion.
 *
 * Se necesita para registrar en el Kardex cuando se revierte una venta,
 * distinguiéndolo de una compra normal.
 *
 * Laravel implementa enum en PostgreSQL como VARCHAR + CHECK constraint,
 * así que hay que reemplazar el constraint en lugar de usar ALTER TYPE.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE kardex DROP CONSTRAINT IF EXISTS kardex_tipo_transaccion_check');
            DB::statement(
                "ALTER TABLE kardex ADD CONSTRAINT kardex_tipo_transaccion_check
                 CHECK (tipo_transaccion IN ('COMPRA', 'VENTA', 'AJUSTE', 'APERTURA', 'REVERSA'))"
            );
        }
        // SQLite no impone CHECK constraints — no requiere cambio de esquema
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE kardex DROP CONSTRAINT IF EXISTS kardex_tipo_transaccion_check');
            DB::statement(
                "ALTER TABLE kardex ADD CONSTRAINT kardex_tipo_transaccion_check
                 CHECK (tipo_transaccion IN ('COMPRA', 'VENTA', 'AJUSTE', 'APERTURA'))"
            );
        }
    }
};
