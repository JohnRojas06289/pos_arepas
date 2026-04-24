<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductoService
{
    /**
     * Crear un Registro
     */
    public function crearProducto(array $data): Producto
    {
        $producto = Producto::create([
            'codigo' => $data['codigo'],
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'img_path' => isset($data['img_path']) && $data['img_path']
                ? $this->handleUploadImage($data['img_path'])
                : null,
            'marca_id' => $data['marca_id'],
            'categoria_id' => $data['categoria_id'],
            'presentacione_id' => $data['presentacione_id'],
        ]);

        Inventario::create([
            'producto_id' => $producto->id,
            'cantidad'    => 0,
        ]);

        return $producto;
    }

    /**
     * Editar un registro
     */
    public function editarProducto(array $data, Producto $producto): Producto
    {

        $producto->update([
            'codigo' => $data['codigo'],
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'img_path' => isset($data['img_path']) && $data['img_path']
                ? $this->handleUploadImage($data['img_path'], $producto->img_path)
                : $producto->img_path,
            'marca_id' => $data['marca_id'],
            'categoria_id' => $data['categoria_id'],
            'presentacione_id' => $data['presentacione_id'],
        ]);

        return $producto;
    }


    /**
     * Eliminar un producto y su imagen asociada
     */
    public function eliminarProducto(Producto $producto): void
    {
        if ($producto->img_path) {
            $disk = config('filesystems.disks.cloudinary.cloud_name') ? 'cloudinary' : config('filesystems.default');
            if (Storage::disk($disk)->exists($producto->img_path)) {
                Storage::disk($disk)->delete($producto->img_path);
            }
        }

        $producto->delete();
    }

    /**
     * Guarda una imagen en el Storage
     *
     */
    private function handleUploadImage(UploadedFile $image, $img_path = null): string
    {
        // FORCE Cloudinary if properly configured (Vercel Fix)
        // We use config() because env() returns null if config is cached
        $isCloudinaryConfigured = config('filesystems.disks.cloudinary.cloud_name');
        $defaultDisk = config('filesystems.default');
        
        $disk = $isCloudinaryConfigured ? 'cloudinary' : $defaultDisk;

        // Capture all possible env sources
        $envVar = $_ENV['CLOUDINARY_URL'] ?? 'null';
        $serverVar = $_SERVER['CLOUDINARY_URL'] ?? 'null';
        $getenvVar = getenv('CLOUDINARY_URL') !== false ? 'Present' : 'null';

        if ($disk !== 'cloudinary' && (env('APP_ENV') === 'production' || env('VERCEL'))) {
             // More graceful error handling: Log the issue and throw a validation error
             \Illuminate\Support\Facades\Log::critical("CLOUDINARY MISSING. Env: {$envVar}, Server: {$serverVar}, getenv: {$getenvVar}");
             
             throw \Illuminate\Validation\ValidationException::withMessages([
                 'img_path' => 'Error de configuración del servidor: La variable CLOUDINARY_URL no está definida en Vercel.'
             ]);
        }

        if ($img_path) {
            // Check if file exists before deleting
            if (Storage::disk($disk)->exists($img_path)) {
                Storage::disk($disk)->delete($img_path);
            }
        }

        $name = uniqid() . '.' . $image->getClientOriginalExtension();
        
        // Store in 'productos' folder
        $path = $image->storeAs('productos', $name, $disk);

        if (!$path) {
             throw new \Exception('Error al subir la imagen al disco: ' . $disk);
        }

        return $path;
    }
}
