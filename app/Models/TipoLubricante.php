<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoLubricante extends Model
{
    //
     protected $table = 'tipos_lubricante';
    protected $fillable = ['nombre', 'activo'];
    protected $casts = ['activo' => 'boolean'];

    public function vehiculo() { return $this->hasMany(Vehiculo::class); }
}
