<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SalidaVehiculo extends Model
{

    use LogsActivity;


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Salida Vehiculos'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }


    protected $table = 'salidas_vehiculos';

    protected $fillable = [
        'guardia_id',
        'vehiculo_id',
        'conductor_id',
        'tipo_combustible',
        'hora_sale',
        'hora_entra',
        'kms_sale',
        'kms_entra',
        'kms_recorridos',
        'litros',
        'consumo_usado',
        'comision',
    ];

    protected $casts = [
        'hora_sale' => 'datetime:H:i',
        'hora_entra' => 'datetime:H:i',
        'kms_recorridos' => 'integer',
        'litros' => 'decimal:2',
        'consumo_usado' => 'decimal:4',
    ];

    // Relaciones
    public function guardia()
    {
        return $this->belongsTo(Guard::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class)->withTrashed();
    }

    public function conductor()
    {
        return $this->belongsTo(Conductor::class)->withTrashed();
    }

    /**
     * Relación: una salida puede tener una boleta de cierre.
     */
    public function boletaCierre()
    {
        return $this->hasOne(BoletaCierre::class, 'salida_id');
    }

    /**
     * Attribute: devuelve true si la salida tiene retorno registrado (boleta o datos en la salida).
     */
    public function getTieneBoletaAttribute(): bool
    {
        return $this->boletaCierre !== null || ($this->hora_entra !== null && $this->kms_entra !== null);
    }

    /**
     * Attribute: estado de la salida ('pendiente' o 'cerrada').
     */
    public function getEstadoAttribute(): string
    {
        return $this->tiene_boleta ? 'cerrada' : 'pendiente';
    }

    // Cálculo automático de kms y litros
    protected static function booted()
    {
        static::saving(function ($model) {
            $model->calcularKmsYlitros();
        });
    }

    public function calcularKmsYlitros()
    {
        $vehiculo = $this->vehiculo;

        if (!$vehiculo || $this->kms_sale === null || $this->kms_entra === null) {
            $this->kms_recorridos = null;
            $this->litros = null;
            $this->consumo_usado = null;
            return;
        }

        if (!$vehiculo->sin_cuentakilometros) {
            $this->kms_recorridos = $this->kms_entra - $this->kms_sale;
        } else {
            $this->kms_recorridos = null;
        }

        if ($vehiculo->consumo_litros_por_km && $this->kms_recorridos) {
            $this->litros = $this->kms_recorridos * $vehiculo->consumo_litros_por_km;
            $this->consumo_usado = $this->litros;
        } else {
            $this->litros = null;
            $this->consumo_usado = null;
        }
    }
}