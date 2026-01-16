<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Empleado extends Model
{
    use HasUuids;
    
    protected $fillable = [
        'razon_social',
        'cargo',
        'img_path'
    ];
    
    protected $guarded = ['id'];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * Guarda la imagen en el servidor
     */
    public function handleUploadImage(UploadedFile $image, $img_path = null): string
    {
        if ($img_path) {
            $relative_path = str_replace('storage/', '', $img_path);

            if (Storage::disk(config('filesystems.default'))->exists($relative_path)) {
                Storage::disk(config('filesystems.default'))->delete($relative_path);
            }
        }

        $name = uniqid() . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('empleados', $name, config('filesystems.default'));
        return $path;
    }
}
