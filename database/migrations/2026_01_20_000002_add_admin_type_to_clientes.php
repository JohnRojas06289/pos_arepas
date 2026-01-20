<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we need to drop the constraint and add it back with the new value
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE clientes DROP CONSTRAINT IF EXISTS clientes_tipo_cliente_check");
            DB::statement("ALTER TABLE clientes ADD CONSTRAINT clientes_tipo_cliente_check CHECK (tipo_cliente IN ('general', 'fiado', 'admin'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE clientes DROP CONSTRAINT IF EXISTS clientes_tipo_cliente_check");
            // Note: This might fail if there are 'admin' records
            DB::statement("ALTER TABLE clientes ADD CONSTRAINT clientes_tipo_cliente_check CHECK (tipo_cliente IN ('general', 'fiado'))");
        }
    }
};
