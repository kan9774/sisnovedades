<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria_id',
        'talla_id',
        'tipo_seguimiento',
        'unidad_medida',
        'stock_minimo',
        'vida_util_meses',
        'atributos',
    ];

    protected $casts = [
        'atributos' => 'array',
        'stock_minimo' => 'integer',
        'vida_util_meses' => 'integer',
    ];
    protected function nombre(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => strtoupper(trim($value)),
        );
    }
    protected function unidadMedida(): Attribute
    {
        return Attribute::make(
            set: fn(?string $value) => $value !== null ? strtoupper(trim($value)) : null,
        );
    }

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

    public function lotesStock(): HasMany
    {
        return $this->hasMany(LoteStock::class);
    }

    public function esIndividual(): bool
    {
        return $this->tipo_seguimiento === 'individual';
    }

    public function esPorCantidad(): bool
    {
        return $this->tipo_seguimiento === 'cantidad';
    }

    public function tieneVidaUtil(): bool
    {
        return ! is_null($this->vida_util_meses);
    }
}
