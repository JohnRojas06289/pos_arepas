&lt;?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app-&gt;make('Illuminate\Contracts\Console\Kernel')-&gt;bootstrap();

$productos = DB::table('productos')-&gt;select('id', 'nombre', 'img_path')-&gt;get();

echo "Productos en la base de datos:\n\n";
foreach ($productos as $producto) {
    echo "ID: {$producto-&gt;id}\n";
    echo "Nombre: {$producto-&gt;nombre}\n";
    echo "img_path: " . ($producto-&gt;img_path ?? 'NULL') . "\n";
    echo "---\n";
}
