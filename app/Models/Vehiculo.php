<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehiculo extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'matricula',
        'tipo_combustible',
        'consumo_litros_por_km',
        'sin_cuentakilometros',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'sin_cuentakilometros' => 'boolean',
        'activo' => 'boolean',
        'consumo_litros_por_km' => 'decimal:4',
    ];
    protected $table = 'vehiculos';
    
    
    
    // Relación con NovedadVehiculo
    public function novedades()
    {
        return $this->hasMany(NovedadVehiculo::class);
    }

    public function resumenesDiarios()
    {
        return $this->hasMany(ResumenVehiculoDiario::class);
    }

    // Helper para obtener nombre completo
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->matricula} - {$this->descripcion}";
    }
}