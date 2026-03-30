<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE compras ALTER COLUMN impuesto TYPE NUMERIC(12,2)');
        DB::statement('ALTER TABLE compras ALTER COLUMN subtotal TYPE NUMERIC(12,2)');
        DB::statement('ALTER TABLE compras ALTER COLUMN total TYPE NUMERIC(12,2)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE compras ALTER COLUMN impuesto TYPE NUMERIC(8,2)');
        DB::statement('ALTER TABLE compras ALTER COLUMN subtotal TYPE NUMERIC(8,2)');
        DB::statement('ALTER TABLE compras ALTER COLUMN total TYPE NUMERIC(8,2)');
    }
};
