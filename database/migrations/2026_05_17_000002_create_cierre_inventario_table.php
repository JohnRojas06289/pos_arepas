<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cierre_inventario', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('caja_id');
            $table->foreign('caja_id')->references('id')->on('cajas')->onDelete('cascade');
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->json('items'); // [{producto_id, nombre, cantidad_sistema, cantidad_fisica, diferencia}]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cierre_inventario');
    }
};
