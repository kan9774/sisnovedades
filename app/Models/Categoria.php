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
        'codigo_abreviatura',
        'categoria_padre_id',
    ];

    /**
     * Palabras que se ignoran al armar la abreviatura automática
     * (no aportan valor identificatorio: "Equipo DE Combate" -> EC, no EDC).
     */
    private const PALABRAS_IGNORADAS = ['de', 'del', 'la', 'el', 'los', 'las', 'y', 'en', 'a', 'al'];

    protected static function booted(): void
    {
        static::creating(function (Categoria $categoria) {
            if (empty($categoria->slug)) {
                $categoria->slug = static::generarSlugUnico($categoria->nombre);
            }

            if (empty($categoria->codigo_abreviatura)) {
                $categoria->codigo_abreviatura = static::generarAbreviaturaUnica($categoria->nombre);
            } else {
                $categoria->codigo_abreviatura = strtoupper($categoria->codigo_abreviatura);
            }
        });

        static::updating(function (Categoria $categoria) {
            if ($categoria->isDirty('nombre') && ! $categoria->isDirty('slug')) {
                $categoria->slug = static::generarSlugUnico($categoria->nombre, $categoria->id);
            }

            if ($categoria->isDirty('codigo_abreviatura') && ! empty($categoria->codigo_abreviatura)) {
                $categoria->codigo_abreviatura = strtoupper($categoria->codigo_abreviatura);
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

    /**
     * Genera una abreviatura única de hasta 3 letras a partir de las
     * iniciales de las palabras significativas del nombre (ignorando
     * artículos/preposiciones). Si el nombre no da para 3 iniciales,
     * completa con las letras siguientes de la primera palabra.
     * Es solo una SUGERENCIA: el usuario puede pisarla libremente desde
     * el formulario, y el bloque `unique` de la validación es lo que
     * realmente garantiza que no se repita.
     */
    protected static function generarAbreviaturaUnica(string $nombre, ?int $ignorarId = null): string
    {
        $palabras = collect(preg_split('/\s+/', trim(Str::ascii($nombre))))
            ->filter()
            ->reject(fn ($palabra) => in_array(mb_strtolower($palabra), self::PALABRAS_IGNORADAS));

        $iniciales = $palabras->map(fn ($palabra) => mb_strtoupper(mb_substr($palabra, 0, 1)))->implode('');

        if (mb_strlen($iniciales) >= 3) {
            $base = mb_substr($iniciales, 0, 3);
        } elseif ($palabras->isNotEmpty()) {
            $primera = mb_strtoupper($palabras->first());
            $base = mb_substr($iniciales . mb_substr($primera, mb_strlen($iniciales)), 0, 3);
        } else {
            $base = 'CAT';
        }

        $base = str_pad($base, 3, 'X'); // por si el nombre tiene menos de 3 letras en total

        $abreviatura = $base;
        $contador = 2;

        while (
            static::query()
                ->where('codigo_abreviatura', $abreviatura)
                ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
                ->exists()
        ) {
            $abreviatura = $base . $contador;
            $contador++;
        }

        return $abreviatura;
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