<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Permission extends Model
{
    //

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Permisos'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }



    protected $fillable = ['name', 'description'];
    protected $table = 'permissions';
    /**
     * The roles that belong to the permission.
     */
    public function rols(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'rol_permission');
    }
}
