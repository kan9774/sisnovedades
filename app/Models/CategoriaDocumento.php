<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CategoriaDocumento extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Categoria Documentos'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }

    protected $fillable = ['nombre', 'slug', 'descripcion'];
    protected $table = 'categorias_documentos';

    protected static function boot()
    {
        parent::boot();

        // Genera el slug automáticamente a partir del nombre si no viene seteado
        static::creating(function (CategoriaDocumento $categoria) {
            if (empty($categoria->slug)) {
                $categoria->slug = static::generarSlugUnico($categoria->nombre);
            }
        });

        // Si se edita el nombre y no se pasó un slug explícito, lo regenera
        static::updating(function (CategoriaDocumento $categoria) {
            if ($categoria->isDirty('nombre') && ! $categoria->isDirty('slug')) {
                $categoria->slug = static::generarSlugUnico($categoria->nombre, $categoria->id);
            }
        });
    }

    /**
     * Genera un slug único para la categoría, agregando un sufijo numérico
     * si ya existe otro registro con el mismo slug.
     */
    protected static function generarSlugUnico(string $nombre, ?int $ignorarId = null): string
    {
        $slugBase = Str::slug($nombre);
        $slug = $slugBase;
        $contador = 1;

        while (
            static::where('slug', $slug)
                ->when($ignorarId, fn ($query) => $query->where('id', '!=', $ignorarId))
                ->exists()
        ) {
            $slug = "{$slugBase}-{$contador}";
            $contador++;
        }

        return $slug;
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }
}