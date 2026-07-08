<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EstadoPaloma extends Model
{
    use LogsActivity;

        public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Estado Paloma'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }

    
    protected $table = 'estados_paloma';

    protected $fillable = ['nombre', 'color', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function palomas(): HasMany
    {
        return $this->hasMany(Paloma::class, 'estado_id');
    }
}