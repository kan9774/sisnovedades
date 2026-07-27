<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteStock extends Model
{
    use HasFactory;

    protected $table = 'lotes_stock';

    protected $fillable = [
        'item_id',
        'ubicacion_id',
        'proveedor_id',
        'fecha_recibido',
        'cantidad_inicial',
        'cantidad_actual',
        'referencia',
    ];

    protected $casts = [
        'fecha_recibido' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    /**
     * Fecha de vencimiento del lote (fecha_recibido + vida_util_meses del
     * ítem). Null si el ítem no tiene vida útil definida.
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

    protected function vencido(): Attribute
    {
        return Attribute::get(fn () => $this->vencimiento !== null && $this->vencimiento->isPast());
    }
}