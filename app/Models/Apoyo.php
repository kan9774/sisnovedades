<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Apoyo extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Apoyos');
    }

    protected $fillable = [
        'tipo_id',
        'organismo_id',
        'documento_novedad_id',
        'documento_texto',
        'desde',
        'hasta',
        'por_documento_novedad_id',
        'por_documento_texto',
        'estado',
        'cumplido_por_id',
        'cumplido_at',
        'registrado_por_id',
        'descripcion',
    ];

    protected $casts = [
        'desde' => 'datetime',
        'hasta' => 'datetime',
        'cumplido_at' => 'datetime',
    ];

    public const ESTADOS = [
        'pendiente',
        'activo',
        'cumplido',
        'suspendido',
        'sin_efecto',
    ];

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoApoyo::class, 'tipo_id');
    }

    public function organismo(): BelongsTo
    {
        return $this->belongsTo(Organismo::class, 'organismo_id');
    }

    public function documentoNovedad(): BelongsTo
    {
        return $this->belongsTo(News::class, 'documento_novedad_id');
    }

    public function porDocumentoNovedad(): BelongsTo
    {
        return $this->belongsTo(News::class, 'por_documento_novedad_id');
    }

    public function cumplidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cumplido_por_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }

    public function unidades(): BelongsToMany
    {
        return $this->belongsToMany(Unidad::class, 'apoyo_unidad', 'apoyo_id', 'unidad_id')
            ->withTimestamps();
    }
}
