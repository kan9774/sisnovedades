<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
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
