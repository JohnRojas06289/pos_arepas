<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('compras', 'impuesto')) {
            DB::statement('ALTER TABLE compras ALTER COLUMN impuesto TYPE NUMERIC(12,2)');
        }

        if (Schema::hasColumn('compras', 'subtotal')) {
            DB::statement('ALTER TABLE compras ALTER COLUMN subtotal TYPE NUMERIC(12,2)');
        }

        if (Schema::hasColumn('compras', 'total')) {
            DB::statement('ALTER TABLE compras ALTER COLUMN total TYPE NUMERIC(12,2)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('compras', 'impuesto')) {
            DB::statement('ALTER TABLE compras ALTER COLUMN impuesto TYPE NUMERIC(8,2)');
        }

        if (Schema::hasColumn('compras', 'subtotal')) {
            DB::statement('ALTER TABLE compras ALTER COLUMN subtotal TYPE NUMERIC(8,2)');
        }

        if (Schema::hasColumn('compras', 'total')) {
            DB::statement('ALTER TABLE compras ALTER COLUMN total TYPE NUMERIC(8,2)');
        }
    }
};
