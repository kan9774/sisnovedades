<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Organismo extends Model
{
    //
    use LogsActivity;


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Organismos'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }



    protected $fillable = ['name'];

    public function novedades(): HasMany
    {
        return $this->hasMany(News::class, 'organismo_id');
    }
}
