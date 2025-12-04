<?php

namespace App\Services;

use App\Models\SyncState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class SyncService
{
    /**
     * Check if internet connection is available
     */
    public function checkConnection(): bool
    {
        try {
            $connected = @fsockopen("www.google.com", 80);
            if ($connected) {
                fclose($connected);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Synchronize all tables
     */
    public function sync(): array
    {
        if (!$this->checkConnection()) {
            return ['status' => 'error', 'message' => 'No internet connection'];
        }

        $tables = [
            'documentos' => \App\Models\Documento::class,
            'monedas' => \App\Models\Moneda::class,
            'comprobantes' => \App\Models\Comprobante::class,
            'ubicaciones' => \App\Models\Ubicacione::class,
            'caracteristicas' => \App\Models\Caracteristica::class,
            'empleados' => \App\Models\Empleado::class,
            'personas' => \App\Models\Persona::class,
            'empresas' => \App\Models\Empresa::class,
            'users' => \App\Models\User::class,
            'cajas' => \App\Models\Caja::class,
            'clientes' => \App\Models\Cliente::class,
            'proveedores' => \App\Models\Proveedore::class,
            'categorias' => \App\Models\Categoria::class,
            'marcas' => \App\Models\Marca::class,
            'presentaciones' => \App\Models\Presentacione::class,
            'productos' => \App\Models\Producto::class,
            'inventarios' => \App\Models\Inventario::class,
            'kardexes' => \App\Models\Kardex::class,
            'compras' => \App\Models\Compra::class,
            'ventas' => \App\Models\Venta::class,
            'movimientos' => \App\Models\Movimiento::class,
            'compra_producto' => \App\Models\CompraProducto::class,
            'producto_venta' => \App\Models\ProductoVenta::class,
            'activity_logs' => \App\Models\ActivityLog::class,
        ];

        $results = [];

        foreach ($tables as $table => $modelClass) {
            try {
                $results[$table] = $this->syncTable($table, $modelClass);
            } catch (\Exception $e) {
                Log::error("Sync failed for table $table: " . $e->getMessage());
                $results[$table] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        return ['status' => 'success', 'details' => $results];
    }

    /**
     * Sync a specific table
     */
    public function syncTable(string $tableName, string $modelClass): array
    {
        $syncState = SyncState::firstOrCreate(['table_name' => $tableName]);
        $lastSync = $syncState->last_sync_at;
        $now = now();

        // 1. PULL: Get changes from Cloud
        $cloudQuery = $modelClass::on('cloud');
        if ($lastSync) {
            $cloudQuery->where('updated_at', '>', $lastSync);
        }
        $cloudRecords = $cloudQuery->get();

        $pulled = 0;
        foreach ($cloudRecords as $record) {
            // Upsert local
            $data = $record->toArray();
            // Remove timestamps if they cause issues, or ensure they are handled
            // Eloquent upsert might be useful, or updateOrCreate
            $modelClass::updateOrCreate(['id' => $data['id']], $data);
            $pulled++;
        }

        // 2. PUSH: Get changes from Local
        $localQuery = $modelClass::query();
        if ($lastSync) {
            $localQuery->where('updated_at', '>', $lastSync);
        }
        $localRecords = $localQuery->get();

        $pushed = 0;
        foreach ($localRecords as $record) {
            // Upsert cloud
            $data = $record->toArray();
            $modelClass::on('cloud')->updateOrCreate(['id' => $data['id']], $data);
            $pushed++;
        }

        // Update sync state
        $syncState->update(['last_sync_at' => $now]);

        return [
            'status' => 'success',
            'pulled' => $pulled,
            'pushed' => $pushed
        ];
    }
}
