<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }
}
