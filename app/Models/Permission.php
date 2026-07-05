<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    //
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
