<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialGrado extends Model
{
    use HasFactory;

    protected $table = 'historial_grados';

    protected $fillable = [
        'user_id',
        'grado_id',
        'tipo',
        'numero_orden',
        'fecha_cambio',
        'resolucion',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_cambio' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class);
    }
}