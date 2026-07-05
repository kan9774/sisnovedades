<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumenCombustibleDiario extends Model
{
    protected $table = 'resumen_combustible_diario';

    protected $fillable = [
        'fecha',
        'guardia_id',
        'tipo_combustible',
        'total_kms',
        'total_litros',
        'cantidad_salidas',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total_kms' => 'integer',
        'total_litros' => 'decimal:2',
        'cantidad_salidas' => 'integer',
    ];

    public function guardia()
    {
        return $this->belongsTo(Guard::class);
    }
}