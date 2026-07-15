<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCombustible extends Model
{
    protected $table = 'tipos_combustible';
    protected $fillable = ['nombre', 'activo'];
    protected $casts = ['activo' => 'boolean'];

    public function vehiculos() { return $this->hasMany(Vehiculo::class); }
}