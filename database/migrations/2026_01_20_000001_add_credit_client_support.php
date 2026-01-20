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
        // Add tipo_cliente to clientes table
        Schema::table('clientes', function (Blueprint $table) {
            $table->enum('tipo_cliente', ['general', 'fiado'])->default('general')->after('persona_id');
        });

        // Add pagado to ventas table
        Schema::table('ventas', function (Blueprint $table) {
            $table->boolean('pagado')->default(true)->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('tipo_cliente');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('pagado');
        });
    }
};
