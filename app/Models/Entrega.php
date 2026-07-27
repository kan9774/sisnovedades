<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entrega extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo',
        'ubicacion_origen_id',
        'ubicacion_destino_id',
        'usuario_id',
        'motivo',
    ];

    public function ubicacionOrigen(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_origen_id');
    }

    public function ubicacionDestino(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_destino_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Las líneas de la entrega: cada una es un movimiento normal
     * (entrada al historial de siempre), agrupadas bajo esta entrega
     * para poder armar el comprobante.
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    public function esEntrega(): bool
    {
        return $this->tipo === 'entrega';
    }

    public function esDevolucion(): bool
    {
        return $this->tipo === 'devolucion';
    }
}