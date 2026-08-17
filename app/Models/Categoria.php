<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
        'categoria_padre_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Categoria $categoria) {
            if (empty($categoria->slug)) {
                $categoria->slug = static::generarSlugUnico($categoria->nombre);
            }
        });

        static::updating(function (Categoria $categoria) {
            if ($categoria->isDirty('nombre') && ! $categoria->isDirty('slug')) {
                $categoria->slug = static::generarSlugUnico($categoria->nombre, $categoria->id);
            }
        });
    }

    /**
     * Genera un slug único a partir del nombre, agregando un sufijo
     * numérico (-2, -3, ...) si ya existe otra categoría con ese slug.
     */
    protected static function generarSlugUnico(string $nombre, ?int $ignorarId = null): string
    {
        $base = Str::slug($nombre);
        $slug = $base;
        $contador = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
                ->exists()
        ) {
            $slug = "{$base}-{$contador}";
            $contador++;
        }

        return $slug;
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_padre_id');
    }

    public function hijas(): HasMany
    {
        return $this->hasMany(Categoria::class, 'categoria_padre_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Todas las categorías hijas de forma recursiva (incluye nietas, etc).
     */
    public function hijasRecursivas(): HasMany
    {
        return $this->hijas()->with('hijasRecursivas');
    }
}