<?php

namespace App\Console\Commands;

use App\Models\Compra;
use App\Models\Inventario;
use Illuminate\Console\Command;

class FixInventarioComprasCommand extends Command
{
    protected $signature = 'inventario:fix-compras
                            {desde : Fecha/hora inicio (Y-m-d H:i:s, en UTC)}
                            {hasta : Fecha/hora fin (Y-m-d H:i:s, en UTC)}
                            {--dry-run : Mostrar cambios sin aplicarlos}';

    protected $description = 'Recalcula el inventario para compras en un rango de tiempo cuyos productos no fueron actualizados';

    public function handle(): int
    {
        $desde   = $this->argument('desde');
        $hasta   = $this->argument('hasta');
        $dryRun  = $this->option('dry-run');

        $this->info("Buscando compras entre {$desde} y {$hasta}" . ($dryRun ? ' [DRY RUN]' : ''));

        $compras = Compra::with('productos')
            ->whereBetween('created_at', [$desde, $hasta])
            ->get();

        if ($compras->isEmpty()) {
            $this->warn('No se encontraron compras en ese rango.');
            return self::SUCCESS;
        }

        $this->info("Compras encontradas: {$compras->count()}");

        foreach ($compras as $compra) {
            $this->line("\nCompra ID: {$compra->id} — {$compra->created_at}");

            foreach ($compra->productos as $producto) {
                $cantidad = $producto->pivot->cantidad;
                $registro = Inventario::where('producto_id', $producto->id)->first();

                if (!$registro) {
                    $this->warn("  [{$producto->nombre}] Sin inventario — se creará con cantidad: {$cantidad}");
                    if (!$dryRun) {
                        Inventario::create([
                            'producto_id' => $producto->id,
                            'cantidad'    => $cantidad,
                            'fecha_vencimiento' => $producto->pivot->fecha_vencimiento,
                        ]);
                    }
                } else {
                    $nueva = $registro->cantidad + $cantidad;
                    $this->info("  [{$producto->nombre}] {$registro->cantidad} + {$cantidad} = {$nueva}");
                    if (!$dryRun) {
                        $registro->increment('cantidad', $cantidad);
                    }
                }
            }
        }

        if ($dryRun) {
            $this->warn("\n[DRY RUN] No se aplicaron cambios. Ejecuta sin --dry-run para confirmar.");
        } else {
            $this->info("\nInventario corregido exitosamente.");
        }

        return self::SUCCESS;
    }
}
