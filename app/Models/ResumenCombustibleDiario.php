<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ResumenCombustibleDiario extends Model
{

    use LogsActivity;


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Resumen Combustible'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }


    protected $table = 'resumen_combustible_diario';

    protected $fillable = [
        'fecha',
        'guardia_id',
        'tipo_combustible',
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
}
