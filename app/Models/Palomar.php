<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Palomar extends Model

{
    protected $table = 'palomares';
    protected $fillable = [
        'nombre', 'ubicacion', 'capacidad_maxima', 'observaciones', 'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function palomas(): HasMany
    {
        return $this->hasMany(Paloma::class);
    }
}