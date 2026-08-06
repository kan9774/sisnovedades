<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JefeUnidad extends Model
{
    use HasFactory;

    protected $table = 'jefes_unidad';

    protected $fillable = [
        'nombre_completo',
        'grado_id',
        'cargo',
        'fecha_desde',
        'fecha_hasta',
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
    ];

    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }

    /**
     * El Jefe vigente al día de hoy (o en una fecha dada, útil para reimprimir
     * un contrato viejo con la autoridad que correspondía en ese momento).
     */
    public static function vigente(?\DateTimeInterface $fecha = null): ?self
    {
        $fecha = $fecha ?? now();

        return static::where('fecha_desde', '<=', $fecha)
            ->where(function ($q) use ($fecha) {
                $q->whereNull('fecha_hasta')->orWhere('fecha_hasta', '>=', $fecha);
            })
            ->orderByDesc('fecha_desde')
            ->first();
    }

    protected static function booted(): void
    {
        static::created(function (JefeUnidad $jefe) {
            // Cerrar el vigente anterior (si no es este mismo)
            static::where('id', '!=', $jefe->id)
                ->whereNull('fecha_hasta')
                ->update([
                    'fecha_hasta' => $jefe->fecha_desde->copy()->subDay(),
                ]);
        });
    }
}