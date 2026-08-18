<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grado extends Model
{
    use HasFactory;
    protected $table = 'grados';
    protected $fillable = ['nombre', 'orden', 'activo'];
    protected $casts = ['activo' => 'boolean'];

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function historialGrados(): HasMany
    {
        return $this->hasMany(HistorialGrado::class);
    }
}