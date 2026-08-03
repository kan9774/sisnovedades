<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovedadRancho extends Model
{
    protected $table = 'novedades_rancho';

    // 1. Añade 'colacion' a los fillable
    protected $fillable = ['guard_id', 'unidad_id', 'desayuno', 'colacion', 'almuerzo', 'merienda', 'cena', 'menu'];

    protected function casts(): array
    {
        return [
            'desayuno' => 'integer',
            'colacion' => 'integer', // 2. Añade colacion al cast
            'almuerzo' => 'integer',
            'merienda' => 'integer',
            'cena'     => 'integer',
        ];
    }

    public function guardia(): BelongsTo
    {
        return $this->belongsTo(Guard::class, 'guard_id');
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    public function getTotalAttribute(): int
    {
        // 3. Incluye colacion en el cálculo del total
        return (int) $this->desayuno + (int) $this->colacion + (int) $this->almuerzo + (int) $this->merienda + (int) $this->cena;
    }
}