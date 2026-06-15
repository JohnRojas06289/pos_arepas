<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_carritos', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID generado en el cliente
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('nombre')->nullable();
            $table->json('items');
            $table->string('metodo_pago')->default('EFECTIVO');
            $table->decimal('dinero_recibido', 12, 2)->default(0);
            $table->decimal('vuelto', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_carritos');
    }
};
