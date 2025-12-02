<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('impuesto');
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->dropColumn('impuesto');
        });

        Schema::table('empresa', function (Blueprint $table) {
            $table->dropColumn(['porcentaje_impuesto', 'abreviatura_impuesto']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('impuesto', 8, 2, true)->default(0);
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->decimal('impuesto')->unsigned()->default(0);
        });

        Schema::table('empresa', function (Blueprint $table) {
            $table->integer('porcentaje_impuesto')->unsigned()->default(0);
            $table->string('abreviatura_impuesto', 5)->default('');
        });
    }
};
