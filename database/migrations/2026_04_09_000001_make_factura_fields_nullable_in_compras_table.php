<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->foreignUuid('comprobante_id')->nullable()->change();
            $table->foreignUuid('proveedore_id')->nullable()->change();
            $table->string('metodo_pago')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->foreignUuid('comprobante_id')->nullable(false)->change();
            $table->foreignUuid('proveedore_id')->nullable(false)->change();
            $table->string('metodo_pago')->nullable(false)->change();
        });
    }
};
