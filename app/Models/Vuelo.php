<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vuelo extends Model
{
    protected $fillable = [
        'fecha',
        'tipo',
        'punto_liberacion',
        'hora_liberacion',
        'condiciones_climaticas',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_liberacion' => 'datetime:H:i',
    ];

    public function palomas(): BelongsToMany
    {
        return $this->belongsToMany(Paloma::class, 'paloma_vuelo')
            ->using(PalomaVuelo::class)
            ->withPivot([
                'estado_anterior_id',
                'distancia_km',
                'hora_llegada',
                'tiempo_vuelo',
                'velocidad_media',
                'posicion',
                'anilla_competicion',
                'observaciones',
            ])
            ->withTimestamps();
    }

    // Promedio de velocidad del grupo que participó en este vuelo
    public function getVelocidadPromedioAttribute()
    {
        $velocidades = $this->palomas->pluck('pivot.velocidad_media')->filter();
        return $velocidades->isNotEmpty() ? round($velocidades->avg(), 2) : null;
    }

    public function getCantidadPalomasAttribute(): int
    {
        return $this->palomas->count();
    }
}