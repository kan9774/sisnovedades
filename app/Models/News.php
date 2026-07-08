<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class News extends Model
{

    use LogsActivity;


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Novedades'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }

    //
    protected $fillable = [
        'guard_id',
        'user_id',
        'type',
        'direction',
        'destino',
        'office_id',   // ← reemplaza 'office'
        'number',
        'time',
        'affair',
        'text',
        'clasification',
        'confirmed',
        'confirmed_at',
        'organismo_id',
    ];
    protected function casts(): array
    {
        return [
            'confirmed'    => 'boolean',
            'confirmed_at' => 'datetime',
            'time'         => 'datetime:H:i',
        ];
    }
    // Constantes
    const TIPOS = ['Radio', 'Fax', 'Correo Electrónico'];

    const DIRECCIONES = ['Recibido', 'Expedido'];

    const CLASIFICACIONES = ['Rutinario', 'Prioritario', 'Urgente', 'Destello'];

    // Relaciones
    public function guardia(): BelongsTo
    {
        return $this->belongsTo(Guard::class, 'guard_id');
    }
    public function escribiente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function adjuntos()
    {
        return $this->hasMany(Attach::class, 'news_id');
    }
    public function organismo(): BelongsTo
    {
        return $this->belongsTo(Organismo::class, 'organismo_id');
    }
    public function logs(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject')->latest();
    }
    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'office_id');
    }



    // Scopes
    public function scopeDeGuardia($query, $guard_id)
    {
        return $query->where('guard_id', $guard_id);
    }
    public function scopeUrgentes($query)
    {
        return $query->where('clasification', ['Urgente', 'Destello']);
    }

    // Helpers
    public function estaConfirmada(): bool
    {
        return $this->confirmed === true;
    }
    public function remitente(): string
    {
        if ($this->direction === 'Expedido') {
            return config('organizacion.nombre');
        }

        return $this->organismo->name ?? 'Sin especificar';
    }
}
