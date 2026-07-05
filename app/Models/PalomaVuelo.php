<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PalomaVuelo extends Pivot
{
    protected $table = 'paloma_vuelo';

    protected $casts = [
        'hora_llegada' => 'datetime:H:i',
        'tiempo_vuelo' => 'datetime:H:i:s',
        'distancia_km' => 'decimal:2',
        'velocidad_media' => 'decimal:2',
    ];
}