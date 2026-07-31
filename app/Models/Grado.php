<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grado extends Model
{
    protected $table = 'grados';
    protected $fillable = ['nombre', 'orden', 'activo'];
    protected $casts = ['activo' => 'boolean'];

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }
}