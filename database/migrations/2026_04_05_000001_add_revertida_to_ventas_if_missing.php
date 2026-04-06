<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ventas', 'revertida')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->boolean('revertida')->default(false)->after('saldo_pendiente');
            });
        }

        DB::table('ventas')
            ->whereNull('revertida')
            ->update(['revertida' => false]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('ventas', 'revertida')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->dropColumn('revertida');
            });
        }
    }
};
