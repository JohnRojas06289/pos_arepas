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
        Schema::create('productos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('codigo', 50)->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('img_path', 2048)->nullable();
            $table->tinyInteger('estado')->default(0);
            $table->decimal('precio', 8, 2, true)->nullable();
            $table->foreignUuid('marca_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignUuid('presentacione_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('categoria_id')->nullable()->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('productos');
    }
};
