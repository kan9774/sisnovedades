<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemUnidad extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'numero_serie',
        'estado',
        'ubicacion_actual_id',
        'responsable_id',
        'fecha_alta',
        'fecha_baja',
    ];
    protected $table = 'item_unidades';
    protected $casts = [
        'fecha_alta' => 'date',
        'fecha_baja' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function ubicacionActual(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_actual_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    public function estaDisponible(): bool
    {
        return $this->estado === 'disponible';
    }
}
