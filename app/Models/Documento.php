<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Documento extends Model
{
    use SoftDeletes, LogsActivity;

        public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Documentos'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }

    protected $table = 'documentos';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'categoria_documento_id',
        'titulo',
        'descripcion',
        'archivo_path',
        'nombre_original',
        'extension',
        'tamanio',
        'subido_por',
        'activo',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaDocumento::class, 'categoria_documento_id');
    }

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function getTamanioLegibleAttribute()
    {
        $bytes = $this->tamanio;
        $unidades = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($unidades) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $unidades[$i];
    }
}
