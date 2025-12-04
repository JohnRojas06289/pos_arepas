<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class MigrateCloudToLocal extends Command
{
    protected $signature = 'migrate:cloud-to-local';
    protected $description = 'Migrate data from Cloud (Int IDs) to Local (UUIDs)';

    protected $idMap = [];

    public function handle()
    {
        if (!$this->confirm('This will wipe your local database and import data from Cloud. Continue?')) {
            return;
        }

        $this->info('Starting migration...');

        // 1. Clear Local Data
        $this->clearLocalData();

        // 2. Import in dependency order with error handling
        $this->safeImport('permissions', function($row) {
            return [
                'id' => $this->getUuid('permissions', $row->id),
                'name' => $row->name,
                'guard_name' => $row->guard_name,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
        });

        $this->safeImport('roles', function($row) {
            return [
                'id' => $this->getUuid('roles', $row->id),
                'name' => $row->name,
                'guard_name' => $row->guard_name,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
        });

        // Role-Permission pivot
        $this->safeImportPivot('role_has_permissions', function($row) {
            return [
                'permission_id' => $this->getUuid('permissions', $row->permission_id),
                'role_id' => $this->getUuid('roles', $row->role_id)
            ];
        });

        $this->safeImport('users', function($row) {
            return [
                'id' => $this->getUuid('users', $row->id),
                'name' => $row->name,
                'email' => $row->email,
                'password' => $row->password,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
        });

        // User-Role pivot
        $this->safeImportPivot('model_has_roles', function($row) {
            return [
                'role_id' => $this->getUuid('roles', $row->role_id),
                'model_type' => User::class,
                'model_id' => $this->getUuid('users', $row->model_id)
            ];
        });

        // Base tables
        $this->safeImport('documentos', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('documentos', $row->id);
            return $data;
        });

        $this->safeImport('monedas', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('monedas', $row->id);
            return $data;
        });

        $this->safeImport('comprobantes', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('comprobantes', $row->id);
            return $data;
        });

        $this->safeImport('ubicaciones', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('ubicaciones', $row->id);
            return $data;
        });

        $this->safeImport('empresa', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('empresa', $row->id);
            if (isset($row->moneda_id) && $row->moneda_id) {
                $data['moneda_id'] = $this->getUuid('monedas', $row->moneda_id);
            }
            // Remove tax columns if they exist in cloud but not local
            unset($data['porcentaje_impuesto']);
            unset($data['abreviatura_impuesto']);
            return $data;
        });

        $this->safeImport('caracteristicas', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('caracteristicas', $row->id);
            return $data;
        });

        $this->safeImport('categorias', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('categorias', $row->id);
            $data['caracteristica_id'] = $this->getUuid('caracteristicas', $row->caracteristica_id);
            return $data;
        });

        $this->safeImport('marcas', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('marcas', $row->id);
            $data['caracteristica_id'] = $this->getUuid('caracteristicas', $row->caracteristica_id);
            return $data;
        });

        $this->safeImport('presentaciones', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('presentaciones', $row->id);
            $data['caracteristica_id'] = $this->getUuid('caracteristicas', $row->caracteristica_id);
            return $data;
        });

        // People
        $this->safeImport('personas', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('personas', $row->id);
            $data['documento_id'] = $this->getUuid('documentos', $row->documento_id);
            return $data;
        });

        $this->safeImport('proveedores', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('proveedores', $row->id);
            $data['persona_id'] = $this->getUuid('personas', $row->persona_id);
            return $data;
        });

        $this->safeImport('clientes', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('clientes', $row->id);
            $data['persona_id'] = $this->getUuid('personas', $row->persona_id);
            return $data;
        });

        // Products
        $this->safeImport('productos', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('productos', $row->id);
            $data['categoria_id'] = $this->getUuid('categorias', $row->categoria_id);
            $data['marca_id'] = $row->marca_id ? $this->getUuid('marcas', $row->marca_id) : null;
            $data['presentacione_id'] = $this->getUuid('presentaciones', $row->presentacione_id);
            return $data;
        });

        // Cajas (needed for ventas foreign key)
        $this->safeImport('cajas', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('cajas', $row->id);
            if (isset($row->user_id) && $row->user_id) {
                $data['user_id'] = $this->getUuid('users', $row->user_id);
            }
            return $data;
        });

        // Transactions
        $this->safeImport('compras', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('compras', $row->id);
            $data['proveedore_id'] = $this->getUuid('proveedores', $row->proveedore_id);
            $data['comprobante_id'] = $this->getUuid('comprobantes', $row->comprobante_id);
            if (isset($row->user_id)) {
                $data['user_id'] = $this->getUuid('users', $row->user_id);
            }
            return $data;
        });

        $this->safeImport('compra_producto', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('compra_producto', $row->id);
            $data['compra_id'] = $this->getUuid('compras', $row->compra_id);
            $data['producto_id'] = $this->getUuid('productos', $row->producto_id);
            return $data;
        });

        $this->safeImport('ventas', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('ventas', $row->id);
            $data['cliente_id'] = $this->getUuid('clientes', $row->cliente_id);
            $data['user_id'] = $this->getUuid('users', $row->user_id);
            $data['comprobante_id'] = $this->getUuid('comprobantes', $row->comprobante_id);
            if (isset($row->caja_id) && $row->caja_id) {
                $data['caja_id'] = $this->getUuid('cajas', $row->caja_id);
            }
            return $data;
        });

        $this->safeImport('producto_venta', function($row) {
            $data = (array)$row;
            $data['id'] = $this->getUuid('producto_venta', $row->id);
            $data['venta_id'] = $this->getUuid('ventas', $row->venta_id);
            $data['producto_id'] = $this->getUuid('productos', $row->producto_id);
            return $data;
        });

        $this->info('Migration completed successfully!');
    }

    private function clearLocalData()
    {
        $this->info('Clearing local data...');
        DB::statement('PRAGMA foreign_keys = OFF;');
        
        $tables = [
            'activity_logs', 'compra_producto', 'producto_venta', 'movimientos', 
            'ventas', 'compras', 'kardexes', 'inventarios', 'productos', 
            'presentaciones', 'marcas', 'categorias', 'proveedores', 'clientes', 
            'cajas', 'empleados', 'empresas', 'monedas', 'ubicaciones', 
            'comprobantes', 'documentos', 'model_has_roles', 'model_has_permissions', 
            'role_has_permissions', 'users', 'roles', 'permissions', 'personas'
        ];

        foreach ($tables as $table) {
            try {
                DB::table($table)->delete();
            } catch (\Exception $e) {
                // Table might not exist, skip
            }
        }

        DB::statement('PRAGMA foreign_keys = ON;');
    }

    private function getUuid($table, $oldId)
    {
        if (!isset($this->idMap[$table][$oldId])) {
            $this->idMap[$table][$oldId] = Str::uuid()->toString();
        }
        return $this->idMap[$table][$oldId];
    }

    private function safeImport($table, $transformer)
    {
        try {
            $this->info("Importing {$table}...");
            $rows = DB::connection('cloud')->table($table)->get();
            foreach ($rows as $row) {
                $data = $transformer($row);
                DB::table($table)->insert($data);
            }
            $this->info("  ✓ Imported " . count($rows) . " records from {$table}");
        } catch (\Exception $e) {
            $this->warn("  ✗ Skipped {$table}: " . $e->getMessage());
        }
    }

    private function safeImportPivot($table, $transformer)
    {
        try {
            $this->info("Importing {$table}...");
            $rows = DB::connection('cloud')->table($table)->get();
            foreach ($rows as $row) {
                $data = $transformer($row);
                DB::table($table)->insert($data);
            }
            $this->info("  ✓ Imported " . count($rows) . " records from {$table}");
        } catch (\Exception $e) {
            $this->warn("  ✗ Skipped {$table}: " . $e->getMessage());
        }
    }
}
