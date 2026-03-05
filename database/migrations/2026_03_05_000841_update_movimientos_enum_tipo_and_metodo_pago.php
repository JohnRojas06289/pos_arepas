<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * In PostgreSQL, enum columns are implemented as CHECK constraints.
     * We drop the old constraints and re-create them with the new allowed values.
     */
    public function up(): void
    {
        // Update tipo: add INGRESO
        DB::statement('ALTER TABLE movimientos DROP CONSTRAINT IF EXISTS movimientos_tipo_check');
        DB::statement("ALTER TABLE movimientos ADD CONSTRAINT movimientos_tipo_check CHECK (tipo IN ('VENTA', 'RETIRO', 'INGRESO'))");

        // Update metodo_pago: add NEQUI, DAVIPLATA, FIADO
        DB::statement('ALTER TABLE movimientos DROP CONSTRAINT IF EXISTS movimientos_metodo_pago_check');
        DB::statement("ALTER TABLE movimientos ADD CONSTRAINT movimientos_metodo_pago_check CHECK (metodo_pago IN ('EFECTIVO', 'TARJETA', 'NEQUI', 'DAVIPLATA', 'FIADO'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE movimientos DROP CONSTRAINT IF EXISTS movimientos_tipo_check');
        DB::statement("ALTER TABLE movimientos ADD CONSTRAINT movimientos_tipo_check CHECK (tipo IN ('VENTA', 'RETIRO'))");

        DB::statement('ALTER TABLE movimientos DROP CONSTRAINT IF EXISTS movimientos_metodo_pago_check');
        DB::statement("ALTER TABLE movimientos ADD CONSTRAINT movimientos_metodo_pago_check CHECK (metodo_pago IN ('EFECTIVO', 'TARJETA'))");
    }
};
