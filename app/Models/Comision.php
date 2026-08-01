<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class Comision extends Model
{
    use HasFactory;

    public const TIPOS_ORDEN = ['O.B.', 'O.Bn.', 'O.C.G.E.', 'Otros'];
    protected $table = 'comisiones';

    protected $fillable = [
        'user_id',
        'unidad_id',
        'fecha_inicio',
        'fecha_fin',
        'tipo_orden',
        'numero_orden',
        'motivo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    /**
     * true si esta comisión sigue vigente (no tiene fecha_fin todavía).
     */
    public function estaVigente(): bool
    {
        return is_null($this->fecha_fin);
    }

    protected static function booted(): void
    {
        static::creating(function (Comision $comision) {
            // Una persona no puede estar en dos comisiones al mismo
            // tiempo. Hay que cerrar la vigente antes de abrir otra.
            $yaTieneVigente = static::where('user_id', $comision->user_id)
                ->whereNull('fecha_fin')
                ->exists();

            if ($yaTieneVigente) {
                throw ValidationException::withMessages([
                    'user_id' => 'Este usuario ya tiene una comisión vigente. Hay que cerrarla antes de abrir una nueva.',
                ]);
            }
        });
    }
}