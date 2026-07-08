<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Rol extends Model
{

    use LogsActivity;


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Roles'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }



    protected $fillable = ['name', 'description'];
    protected $table = 'rols';
    //
    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'rol_permission', 'rol_id', 'permission_id');
    }
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id');
    }
}
