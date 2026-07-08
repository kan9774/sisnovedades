<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PalomaVuelo extends Pivot
{

    use LogsActivity;


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Vuelo Palomas'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }

    protected $table = 'paloma_vuelo';

    protected $casts = [
        'hora_llegada' => 'datetime:H:i',
        'tiempo_vuelo' => 'datetime:H:i:s',
        'distancia_km' => 'decimal:2',
        'velocidad_media' => 'decimal:2',
    ];
}
