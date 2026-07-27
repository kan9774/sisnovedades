<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'proveedor_id',
        'fecha_recibido',
        'fecha_alta',
        'fecha_baja',
    ];
    protected $table = 'item_unidades';
    protected $casts = [
        'fecha_recibido' => 'date',
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

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    public function estaDisponible(): bool
    {
        return $this->estado === 'disponible';
    }

    /**
     * Fecha de vencimiento de esta unidad (fecha_recibido + vida_util_meses
     * del ítem). Null si falta la fecha de recibido o el ítem no tiene
     * vida útil definida.
     */
    protected function vencimiento(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->fecha_recibido || ! $this->item?->vida_util_meses) {
                return null;
            }

            return $this->fecha_recibido->copy()->addMonths($this->item->vida_util_meses);
        });
    }

    public function estaVencida(): bool
    {
        return $this->vencimiento !== null && $this->vencimiento->isPast();
    }
}