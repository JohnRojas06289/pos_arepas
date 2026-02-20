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
            $table->uuid('marca_id')->nullable()->change();
            $table->uuid('presentacione_id')->nullable()->change();
            $table->uuid('categoria_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->uuid('marca_id')->nullable(false)->change();
            $table->uuid('presentacione_id')->nullable(false)->change();
            $table->uuid('categoria_id')->nullable(false)->change();
        });
    }
};
