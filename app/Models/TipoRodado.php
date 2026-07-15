<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoRodado extends Model
{
    protected $table = 'tipos_rodado';
    protected $fillable = ['nombre', 'medida', 'posicion', 'marca', 'presion_recomendada', 'activo'];
    protected $casts = ['activo' => 'boolean', 'presion_recomendada' => 'decimal:2'];

    public function vehiculos() { return $this->hasMany(Vehiculo::class); }

    public function getPosicionLabelAttribute(): string
    {
        return match ($this->posicion) {
            'delantero' => 'Delantero',
            'trasero' => 'Trasero',
            'unico' => 'Único',
            default => '-',
        };
    }
}
