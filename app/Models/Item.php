<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'categoria_id',
        'talla_id',
        'tipo_seguimiento',
        'unidad_medida',
        'stock_minimo',
        'atributos',
    ];

    protected $casts = [
        'atributos' => 'array',
        'stock_minimo' => 'integer',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function talla(): BelongsTo
    {
        return $this->belongsTo(Talla::class);
    }

    public function itemUnidades(): HasMany
    {
        return $this->hasMany(ItemUnidad::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    public function esIndividual(): bool
    {
        return $this->tipo_seguimiento === 'individual';
    }

    public function esPorCantidad(): bool
    {
        return $this->tipo_seguimiento === 'cantidad';
    }
}