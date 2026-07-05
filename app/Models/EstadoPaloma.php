<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoPaloma extends Model
{
    protected $table = 'estados_paloma';

    protected $fillable = ['nombre', 'color', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function palomas(): HasMany
    {
        return $this->hasMany(Paloma::class, 'estado_id');
    }
}