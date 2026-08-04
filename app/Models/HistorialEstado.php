<?php

namespace App\Models;

use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class HistorialEstado extends Model
{
    use HasFactory;

    protected $table = 'historial_estado';

    /**
     * Un militar puede ingresar 1 vez y reingresar hasta 2 veces más
     * (3 altas en total). Al llegar a este número, no se permite un
     * cuarto ingreso nunca más.
     */
    public const MAX_ALTAS = 3;

    protected $fillable = [
        'user_id',
        'tipo',
        'fecha',
        'motivo',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::creating(function (HistorialEstado $historial) {
            if ($historial->tipo === 'alta') {
                $altasPrevias = static::where('user_id', $historial->user_id)
                    ->where('tipo', 'alta')
                    ->count();

                if ($altasPrevias >= self::MAX_ALTAS) {
                    throw ValidationException::withMessages([
                        'tipo' => 'Este usuario ya alcanzó el máximo de ' . self::MAX_ALTAS . ' altas (ingreso + reingresos). No se permite un nuevo ingreso.',
                    ]);
                }
            }
        });

        // Sincronización en un solo sentido: historial_estado manda sobre
        // users.status. Una baja deshabilita al usuario en la app; una
        // alta (ingreso o reingreso) lo vuelve a habilitar.
        static::created(function (HistorialEstado $historial) {
            $historial->user->update([
                'status' => $historial->tipo === 'baja' ? UserStatus::Inactive : UserStatus::Active,
            ]);
        });
    }
    public function causal()
    {
        return $this->belongsTo(CausalBaja::class);
    }
}
