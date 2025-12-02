<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CheckProductImages extends Command
{
    protected $signature = 'productos:check-images';
    protected $description = 'Check product images and their database paths';

    public function handle()
    {
        $this->info('=== Checking Products and Images ===');
        $this->newLine();

        // Get all products
        $productos = DB::table('productos')->select('id', 'nombre', 'img_path')->get();

        $this->info('Products in database:');
        $this->table(
            ['ID', 'Name', 'img_path'],
            $productos->map(fn($p) => [$p->id, $p->nombre, $p->img_path ?? 'NULL'])
        );

        $this->newLine();
        $this->info('Images in storage/app/public/productos:');
        
        $files = Storage::disk('public')->files('productos');
        foreach ($files as $file) {
            $this->line('- ' . basename($file));
        }

        $this->newLine();
        $this->warn('=== Important Information ===');
        $this->line('The img_path in the database should store the path relative to storage/app/public/');
        $this->line('Example: "productos/692dd2d4d2916.webp"');
        $this->line('NOT: "storage/productos/692dd2d4d2916.webp"');
        
        return 0;
    }
}
