<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ResumenVehiculoDiario extends Model
{

    use LogsActivity;



    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Resumen Vehiculo Diario'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }



    protected $table = 'resumen_vehiculos_diario';

    protected $fillable = [
        'fecha',
        'guardia_id',
        'vehiculo_id',
        'total_kms',
        'total_litros',
        'cantidad_salidas',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total_kms' => 'integer',
        'total_litros' => 'decimal:2',
        'cantidad_salidas' => 'integer',
    ];

    public function guardia()
    {
        return $this->belongsTo(Guard::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }
}
