<?php

namespace App\Services;

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
     * Guarda una imagen en el Storage
     * 
     */
    private function handleUploadImage(UploadedFile $image, $img_path = null): string
    {
        \Illuminate\Support\Facades\Log::info('Inicio de subida de imagen', [
            'original_name' => $image->getClientOriginalName(),
            'size' => $image->getSize(),
            'disk' => config('filesystems.default'),
            'cloudinary_config' => config('filesystems.disks.cloudinary'),
        ]);

        if ($img_path) {
            // Remove 'storage/' prefix if it exists for backward compatibility
            $relative_path = str_replace('storage/', '', $img_path);

            if (Storage::disk(config('filesystems.default'))->exists($relative_path)) {
                Storage::disk(config('filesystems.default'))->delete($relative_path);
                \Illuminate\Support\Facades\Log::info('Imagen anterior eliminada', ['path' => $relative_path]);
            }
        }

        $name = uniqid() . '.' . $image->getClientOriginalExtension();
        // Store only the relative path without 'storage/' prefix
        try {
            $path = $image->storeAs('productos', $name, config('filesystems.default'));
            
            if ($path === false) {
                throw new \Exception('El almacenamiento devolvió false. Verifique permisos de escritura en el bucket.');
            }

            \Illuminate\Support\Facades\Log::info('Imagen subida exitosamente', ['path' => $path]);
            return $path;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al subir imagen', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
