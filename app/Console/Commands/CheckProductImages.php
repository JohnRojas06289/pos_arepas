&lt;?php

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
        $this-&gt;info('=== Checking Products and Images ===');
        $this-&gt;newLine();

        // Get all products
        $productos = DB::table('productos')-&gt;select('id', 'nombre', 'img_path')-&gt;get();

        $this-&gt;info('Products in database:');
        $this-&gt;table(
            ['ID', 'Name', 'img_path'],
            $productos-&gt;map(fn($p) =&gt; [$p-&gt;id, $p-&gt;nombre, $p-&gt;img_path ?? 'NULL'])
        );

        $this-&gt;newLine();
        $this-&gt;info('Images in storage/app/public/productos:');
        
        $files = Storage::disk('public')-&gt;files('productos');
        foreach ($files as $file) {
            $this-&gt;line('- ' . basename($file));
        }

        $this-&gt;newLine();
        $this-&gt;warn('=== Important Information ===');
        $this-&gt;line('The img_path in the database should store the path relative to storage/app/public/');
        $this-&gt;line('Example: "productos/692dd2d4d2916.webp"');
        $this-&gt;line('NOT: "storage/productos/692dd2d4d2916.webp"');
        
        return 0;
    }
}
