<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Palomar extends Model

{

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Palomar'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }


    protected $table = 'palomares';
    protected $fillable = [
        'nombre',
        'ubicacion',
        'capacidad_maxima',
        'observaciones',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function palomas(): HasMany
    {
        return $this->hasMany(Paloma::class);
    }
}
