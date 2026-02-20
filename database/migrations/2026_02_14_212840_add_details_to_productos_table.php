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
        Schema::table('productos', function (Blueprint $table) {
            $table->string('color')->nullable()->after('descripcion');
            $table->string('material')->nullable()->after('color');
            $table->enum('genero', ['Hombre', 'Mujer', 'Unisex'])->default('Unisex')->after('material');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['color', 'material', 'genero']);
        });
    }
};
