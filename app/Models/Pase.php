<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class Pase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'unidad_id',
        'fecha_desde',
        'fecha_hasta',
        'numero_orden',
        'motivo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
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
     * A partir de la fecha en la que se produce el pase (la que tipea
     * quien lo carga), calcula el primer día del mes siguiente, que es
     * el fecha_desde real del nuevo pase. El pase anterior queda
     * cerrado el último día del mes en curso automáticamente (ver
     * booted(), no hace falta calcularlo acá).
     */
    public static function fechaDesdeParaPase(Carbon|string $fechaProduccion): Carbon
    {
        return Carbon::parse($fechaProduccion)->addMonthNoOverflow()->startOfMonth();
    }

    protected static function booted(): void
    {
        static::creating(function (Pase $pase) {
            // Si tiene una comisión vigente, hay que cerrarla a mano
            // primero. No la cerramos solos automáticamente: es una
            // decisión administrativa aparte.
            $tieneComisionVigente = Comision::where('user_id', $pase->user_id)
                ->whereNull('fecha_fin')
                ->exists();

            if ($tieneComisionVigente) {
                throw ValidationException::withMessages([
                    'unidad_id' => 'Este usuario tiene una comisión vigente. Hay que cerrarla antes de registrar un pase.',
                ]);
            }
        });

        static::created(function (Pase $pase) {
            // Cierra el pase anterior vigente de este usuario (si existe)
            // el día inmediato anterior al fecha_desde del nuevo. Como
            // fecha_desde siempre es el 1° de un mes, restar un día da
            // automáticamente el último día del mes anterior — sin overlap
            // ni hueco entre ambos pases.
            static::where('user_id', $pase->user_id)
                ->where('id', '!=', $pase->id)
                ->whereNull('fecha_hasta')
                ->latest('fecha_desde')
                ->first()
                ?->update(['fecha_hasta' => $pase->fecha_desde->copy()->subDay()]);

            // Sincronización automática con users.unidad_id (mismo patrón
            // que grado y estado): el pase vigente manda sobre el campo
            // cacheado.
            $pase->user->update(['unidad_id' => $pase->unidad_id]);
        });
    }
}