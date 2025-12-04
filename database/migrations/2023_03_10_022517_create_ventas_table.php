<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cliente_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('comprobante_id')->constrained()->cascadeOnDelete();
            $table->string('numero_comprobante')->unique();
            $table->enum('metodo_pago', ['EFECTIVO', 'TARJETA']);
            $table->dateTime('fecha_hora');
            $table->decimal('subtotal', 8, 2, true);
            $table->decimal('impuesto', 8, 2, true);
            $table->decimal('total', 8, 2, true);
            $table->decimal('monto_recibido', 8, 2, true);
            $table->decimal('vuelto_entregado', 8, 2, true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ventas');
    }
};
