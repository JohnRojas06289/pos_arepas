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
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('saldo_pendiente', 10, 2)->default(0)->after('pagado');
        });

        // Initialize saldo_pendiente for existing records
        DB::statement("UPDATE ventas SET saldo_pendiente = total WHERE pagado = 0");
        DB::statement("UPDATE ventas SET saldo_pendiente = 0 WHERE pagado = 1");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('saldo_pendiente');
        });
    }
};
