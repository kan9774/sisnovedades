<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalidaVehiculo extends Model
{
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

        if (!$vehiculo->sin_cuentakilometros && $this->kms_sale && $this->kms_entra) {
            $this->kms_recorridos = $this->kms_entra - $this->kms_sale;
        } else {
            $this->kms_recorridos = null;
        }

        if ($vehiculo->consumo_litros_por_km && $this->kms_recorridos) {
            $this->litros = $this->kms_recorridos * $vehiculo->consumo_litros_por_km;
            $this->consumo_usado = $vehiculo->consumo_litros_por_km;
        } else {
            $this->litros = null;
            $this->consumo_usado = null;
        }
    }
}