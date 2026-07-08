<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Paloma extends Model
{

use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Paloma'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }

    
    protected $fillable = [
        'palomar_id',
        'anilla',
        'nombre',
        'fecha_nacimiento',
        'sexo',
        'color',
        'raza',
        'origen',
        'padre_id',
        'madre_id',
        'estado_id',
        'observaciones',
        'estado_sanitario'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function palomar(): BelongsTo
    {
        return $this->belongsTo(Palomar::class);
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoPaloma::class, 'estado_id');
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Paloma::class, 'padre_id');
    }

    public function madre(): BelongsTo
    {
        return $this->belongsTo(Paloma::class, 'madre_id');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialPaloma::class);
    }

    public function vuelos(): BelongsToMany
{
    return $this->belongsToMany(Vuelo::class, 'paloma_vuelo')
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

    // Accesor: saber si es pichón (<6 meses)
    public function getEsPichonAttribute(): bool
    {
        return $this->fecha_nacimiento && $this->fecha_nacimiento->diffInMonths(now()) < 6;
    }

    // Scopes útiles
    public function scopeActivas($query)
    {
        return $query->whereHas('estado', fn($q) => $q->where('nombre', 'Activa'));
    }

    public function scopePichones($query)
    {
        return $query->where('fecha_nacimiento', '>', now()->subMonths(6));
    }

    public function scopeAdultos($query)
    {
        return $query->where('fecha_nacimiento', '<=', now()->subMonths(6));
    }
}
