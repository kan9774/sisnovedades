<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehiculoActa extends Model
{
    protected $fillable = [
        'vehiculo_id',
        'path',
        'nombre_original',
        'tamano_bytes',
    ];

    protected $casts = [
        'tamano_bytes' => 'integer',
    ];

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class);
    }
}
