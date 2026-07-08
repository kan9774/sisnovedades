<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Vehiculo extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Vehiculos'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }

    protected $fillable = [
        'matricula',
        'marca',
        'modelo',
        'vehiculo',
        'numero_chasis',
        'numero_motor',
        'ejes',
        'tipo_vehiculo_id',
        'unidad_id',
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

    // Relación con Unidad (unidad a la que pertenece el vehículo)
    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

    // Relación con TipoVehiculo (tipo de vehículo)
    public function tipoVehiculo()
    {
        return $this->belongsTo(TipoVehiculo::class);
    }

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
            'verde'    => '🟢 OK',
            'amarillo' => '🟡 Observación',
            'rojo'     => '🔴 Fuera de servicio',
            'negro'    => '⚫ Dado de baja',
            default    => 'Desconocido',
        };
    }

    public function getEstadoBadgeClassAttribute(): string
    {
        return match ($this->estado) {
            'verde'    => 'badge badge-success',
            'amarillo' => 'badge badge-warning',
            'rojo'     => 'badge badge-danger',
            'negro'    => 'badge badge-dark',
            default    => 'badge badge-secondary',
        };
    }
}