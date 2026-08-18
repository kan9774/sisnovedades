<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoRodado extends Model
{
    use HasFactory;

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
