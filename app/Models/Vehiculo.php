<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehiculo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'matricula',
        'marca',
        'modelo',
        'color',
        'numero_chasis',
        'numero_motor',
        'ejes',
        'tipo_combustible',
        'consumo_litros_por_km',
        'sin_cuentakilometros',
        'descripcion',
        'estado',
        'activo',
    ];

    protected $casts = [
        'sin_cuentakilometros' => 'boolean',
        'activo' => 'boolean',
        'consumo_litros_por_km' => 'decimal:4',
        'ejes' => 'integer',
    ];

    protected $table = 'vehiculos';

    // Relación con SalidaVehiculo (salidas registradas por guardia)
    public function salidas()
    {
        return $this->hasMany(SalidaVehiculo::class);
    }

    public function resumenesDiarios()
    {
        return $this->hasMany(ResumenVehiculoDiario::class);
    }

    // Relación con mantenimientos
    public function mantenimientos()
    {
        return $this->hasMany(MantenimientoVehiculo::class)->orderByDesc('fecha');
    }

    // Helper para obtener nombre completo
    public function getNombreCompletoAttribute(): string
    {
        $marcaModelo = trim("{$this->marca} {$this->modelo}");

        return $marcaModelo !== ''
            ? "{$this->matricula} - {$marcaModelo}"
            : "{$this->matricula} - {$this->descripcion}";
    }
    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'verde'  => '🟢 OK',
            'amarillo' => '🟡 Observación',
            'rojo'   => '🔴 Fuera de servicio',
            'negro'  => '⚫ Dado de baja',
            default  => 'Desconocido',
        };
    }
    public function getEstadoBadgeClassAttribute(): string
    {
        return match ($this->estado) {
            'verde'  => 'badge bg-success',
            'amarillo' => 'badge bg-warning text-dark',
            'rojo'   => 'badge bg-danger',
            'negro'  => 'badge bg-dark',
            default  => 'badge bg-secondary',
        };
    }
}
