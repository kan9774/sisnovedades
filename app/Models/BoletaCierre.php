<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoletaCierre extends Model
{
    protected $table = 'boletas_cierre';

    protected $fillable = [
        'salida_id',
        'guardia_id',
        'fecha_entra',
        'hora_entra',
        'kms_entra',
        'observaciones',
    ];

    protected $casts = [
        'fecha_entra' => 'date',
        'hora_entra' => 'datetime:H:i',
        'kms_entra' => 'integer',
    ];

    /**
     * Relación: una boleta pertenece a una salida de vehículo.
     */
    public function salida(): BelongsTo
    {
        return $this->belongsTo(SalidaVehiculo::class, 'salida_id');
    }

    /**
     * Relación: la boleta pertenece a la guardia donde se registró el cierre.
     */
    public function guardia(): BelongsTo
    {
        return $this->belongsTo(Guard::class, 'guardia_id');
    }

    /**
     * Al crear o actualizar la boleta, se actualiza automáticamente
     * la salida con los datos de regreso y se recalculan kms_recorridos y litros.
     */
    protected static function booted(): void
    {
        static::saving(function ($boleta) {
            $salida = $boleta->salida;

            // Actualizar datos de regreso en la salida
            $salida->hora_entra = $boleta->hora_entra;
            $salida->kms_entra = $boleta->kms_entra;

            // Recalcular kms_recorridos y litros
            $salida->calcularKmsYlitros();

            $salida->save();
        });
    }
}
