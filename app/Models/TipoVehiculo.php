<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TipoVehiculo extends Model
{
    use HasFactory, LogsActivity;


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Tipo Vehiculo'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }



    protected $table = 'tipos_vehiculo';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class);
    }
}
