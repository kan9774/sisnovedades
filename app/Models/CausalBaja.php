<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CausalBaja extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'permite_reingreso',
        'activo',
    ];
    protected $table = 'causales_baja';

    protected function casts(): array
    {
        return [
            'permite_reingreso' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function historialEstados()
    {
        return $this->hasMany(HistorialEstado::class, 'causal_id');
    }
}