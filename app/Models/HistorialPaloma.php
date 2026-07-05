<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialPaloma extends Model
{
    protected $fillable = [
        'paloma_id', 'evento', 'estado_anterior_id', 'estado_nuevo_id',
        'destino', 'fecha_evento', 'observaciones', 'user_id'
    ];

    protected $casts = [
        'fecha_evento' => 'date',
    ];

    public function paloma(): BelongsTo
    {
        return $this->belongsTo(Paloma::class);
    }

    public function estadoAnterior(): BelongsTo
    {
        return $this->belongsTo(EstadoPaloma::class, 'estado_anterior_id');
    }

    public function estadoNuevo(): BelongsTo
    {
        return $this->belongsTo(EstadoPaloma::class, 'estado_nuevo_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}