<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PostgreSQL requires explicit USING clause for boolean to integer conversion
        // First drop the default constraint
        DB::statement('ALTER TABLE cajas ALTER COLUMN estado DROP DEFAULT');
        // Then change the type with explicit conversion
        DB::statement('ALTER TABLE cajas ALTER COLUMN estado TYPE smallint USING (CASE WHEN estado THEN 1 ELSE 0 END)');
        // Finally set the new default
        DB::statement('ALTER TABLE cajas ALTER COLUMN estado SET DEFAULT 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            // Revert back to boolean
            $table->boolean('estado')->default(true)->change();
        });
    }
};
