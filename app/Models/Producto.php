<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Producto extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'img_path',
        'estado',
        'precio',
        'marca_id',
        'presentacione_id',
        'categoria_id',
        'color',
        'material',
        'genero'
    ];

    public function compras(): BelongsToMany
    {
        return $this->belongsToMany(Compra::class)
            ->withTimestamps()
            ->withPivot('cantidad', 'precio_compra', 'fecha_vencimiento');
    }

    public function ventas(): BelongsToMany
    {
        return $this->belongsToMany(Venta::class)
            ->withTimestamps()
            ->withPivot('cantidad', 'precio_venta');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class);
    }

    public function presentacione(): BelongsTo
    {
        return $this->belongsTo(Presentacione::class);
    }

    public function inventario(): HasOne
    {
        return $this->hasOne(Inventario::class);
    }

    public function kardex(): HasMany
    {
        return $this->hasMany(Kardex::class);
    }

    protected static function booted()
    {
        static::creating(function ($producto) {
            //Si no se propociona un código, generar uno único
            if (empty($producto->codigo)) {
                $producto->codigo = self::generateUniqueCode();
            }
        });
    }

    /**
     * Genera un código único para el producto
     */
    private static function generateUniqueCode(): string
    {
        do {
            $code = str_pad(random_int(0, 9999999999), 12, '0', STR_PAD_LEFT);
        } while (self::where('codigo', $code)->exists());

        return $code;
    }

    /**
     * Accesor para obtener el código, nombre y presentación del producto
     */
    public function getNombreCompletoAttribute(): string
    {
        $sigla = $this->presentacione?->sigla ?? '-';
        return "Código: {$this->codigo} - {$this->nombre} - Presentación: {$sigla}";
    }

    public function getImageUrlAttribute(): string
    {
        if (empty($this->img_path)) {
            return '';
        }

        $path = trim((string) $this->img_path);

        // If path is already a URL, force https when possible
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return Str::startsWith($path, 'http://')
                ? preg_replace('/^http:\/\//i', 'https://', $path)
                : $path;
        }

        // Legacy local paths
        if (Str::startsWith($path, 'storage/')) {
            return asset($path);
        }
        if (Str::startsWith($path, '/storage/')) {
            return asset(ltrim($path, '/'));
        }
        if (Str::startsWith($path, 'public/')) {
            return Storage::url(Str::after($path, 'public/'));
        }

        $cloudName = config('filesystems.disks.cloudinary.cloud_name');

        if (!$cloudName) {
             $cloudName = parse_url(env('CLOUDINARY_URL'), PHP_URL_HOST);
        }

        // Normalize malformed cloud names if they include extra URL fragments
        if ($cloudName && preg_match('/res\.cloudinary\.com\/([^\/]+)/', $cloudName, $matches)) {
            $cloudName = $matches[1];
        }
        if ($cloudName) {
            $cloudName = trim((string) $cloudName, " \t\n\r\0\x0B/@");
        }

        if ($cloudName) {
            return "https://res.cloudinary.com/{$cloudName}/image/upload/{$path}";
        }

        return Storage::url($path);
    }
}
