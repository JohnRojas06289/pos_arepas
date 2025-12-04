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
        Schema::create('compra_producto', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('compra_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('producto_id')->constrained()->onDelete('cascade');
            $table->integer('cantidad')->unsigned();
            $table->decimal('precio_compra', 10, 2, true);
            $table->date('fecha_vencimiento')->nullable();
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
        Schema::dropIfExists('compra_producto');
    }
};
