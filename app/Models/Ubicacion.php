<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ubicacion extends Model
{
    use HasFactory;
    protected $table = 'ubicaciones';

    protected $fillable = [
        'nombre',
        'tipo',
        'referencia_id',
        'es_general',
    ];
    protected $casts = [
        'es_general' => 'boolean',
    ];
    public static function general(): self
    {
        return static::where('es_general', true)->firstOrFail();
    }

    public function itemUnidades(): HasMany
    {
        return $this->hasMany(ItemUnidad::class, 'ubicacion_actual_id');
    }

    public function movimientosOrigen(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'ubicacion_origen_id');
    }

    public function movimientosDestino(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'ubicacion_destino_id');
    }

    /**
     * Resuelve el modelo real al que apunta esta ubicación
     * (Vehiculo, Oficina o User), según el campo `tipo`.
     * No es una relación polimórfica de Eloquent porque cada
     * tipo vive en una tabla distinta con su propio modelo.
     */
    public function referenciable(): ?Model
    {
        return match ($this->tipo) {
            'vehiculo' => Vehiculo::find($this->referencia_id),
            'oficina' => Oficina::find($this->referencia_id),
            'persona' => User::find($this->referencia_id),
            default => null, // 'deposito' no tiene entidad propia
        };
    }
}
