&lt;?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app-&gt;make('Illuminate\Contracts\Console\Kernel')-&gt;bootstrap();

echo "=== Checking Products and Images ===\n\n";

// Get all products
$productos = DB::table('productos')-&gt;select('id', 'nombre', 'img_path')-&gt;get();

echo "Products in database:\n";
foreach ($productos as $producto) {
    echo sprintf("ID: %d | Name: %s | img_path: %s\n", 
        $producto-&gt;id, 
        $producto-&gt;nombre, 
        $producto-&gt;img_path ?? 'NULL'
    );
}

echo "\n\nImages in storage/app/public/productos:\n";
$storagePath = __DIR__ . '/storage/app/public/productos';
if (is_dir($storagePath)) {
    $files = scandir($storagePath);
    foreach ($files as $file) {
        if ($file !== '.' &amp;&amp; $file !== '..') {
            echo "- $file\n";
        }
    }
} else {
    echo "Directory not found!\n";
}

echo "\n\n=== Suggested Fix ===\n";
echo "You need to update the productos table with the correct img_path values.\n";
echo "The img_path should contain just the filename (e.g., '692dd2d4d2916.webp')\n";
echo "NOT the full path like 'storage/productos/692dd2d4d2916.webp'\n\n";

echo "Example SQL to update a product:\n";
echo "UPDATE productos SET img_path = 'productos/692dd2d4d2916.webp' WHERE id = 1;\n";
