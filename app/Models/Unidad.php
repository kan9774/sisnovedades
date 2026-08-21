<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Unidad extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'unidades';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Unidades'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }

    protected $fillable = [
        'nombre',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function unidadModulos(): HasMany
    {
        return $this->hasMany(UnidadModulo::class);
    }

    /**
     * Unidades activas curadas para un módulo (tabla pivot unidad_modulo).
     *
     * Claves válidas: UnidadModulo::MODULOS.
     * Devuelve activo=true AND EXISTS(pivot con ese módulo), ordenado por nombre.
     */
    public function scopeCuradasPara(Builder $query, string $modulo): void
    {
        $query->where('activo', true)
            ->whereHas('unidadModulos', fn ($q) => $q->where('modulo', $modulo))
            ->orderBy('nombre');
    }
}