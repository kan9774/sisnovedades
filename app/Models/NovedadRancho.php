<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovedadRancho extends Model
{
    protected $table = 'novedades_rancho';

    protected $fillable = ['guard_id', 'unidad_id', 'desayuno', 'almuerzo', 'merienda', 'cena', 'menu'];

    protected function casts(): array
    {
        return [
            'desayuno' => 'integer',
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
        return (int) $this->desayuno + (int) $this->almuerzo + (int) $this->merienda + (int) $this->cena;
    }
}