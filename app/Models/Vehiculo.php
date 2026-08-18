<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Vehiculo extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected static function booted(): void
    {
        // Borrar la carpeta de actas SOLO cuando se hace forceDelete().
        // NO en el soft delete normal (delete()), porque sin papelera en la UI
        // un vehículo eliminado queda invisible pero restaurable; si borramos
        // las actas en el soft delete, al restaurar el registro quedaría
        // huérfano sin sus archivos adjuntos.
        static::forceDeleting(function (Vehiculo $vehiculo) {
            $carpeta = $vehiculo->carpetaActas();
            if (Storage::disk('public')->exists($carpeta)) {
                Storage::disk('public')->deleteDirectory($carpeta);
            }
        });
    }

    /**
     * Ruta relativa de la carpeta de actas de este vehículo en storage.
     * Ejemplo: 'actas/AA_123'
     */
    public function carpetaActas(): string
    {
        $matriculaSanitizada = trim(
            preg_replace('/_+/', '_', strtoupper(preg_replace('/[^A-Z0-9]/', '_', $this->matricula))),
            '_'
        );

        return 'actas/' . $matriculaSanitizada;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Vehiculos'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }

    protected $fillable = [
        'matricula',
        'marca',
        'modelo',
        'vehiculo',
        'numero_chasis',
        'numero_motor',
        'ejes',
        'tipo_vehiculo_id',
        'tipo_combustible_id',
        'tipo_lubricante_id',
        'tipo_rodado_id', // <-- actualizado
        'unidad_id',
        'consumo_litros_por_km',
        'sin_cuentakilometros',
        'descripcion',
        'acta',
        'estado',
        'activo',
    ];

    protected $casts = [
        'sin_cuentakilometros' => 'boolean',
        'activo' => 'boolean',
        'consumo_litros_por_km' => 'decimal:4',
        'ejes' => 'integer',
    ];

    protected $table = 'vehiculos';

    // Relación con Unidad (unidad a la que pertenece el vehículo)
    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

    // Relación con TipoVehiculo (tipo de vehículo)
    public function tipoVehiculo()
    {
        return $this->belongsTo(TipoVehiculo::class);
    }

    // Relación con SalidaVehiculo (salidas registradas por guardia)
    public function salidas()
    {
        return $this->hasMany(SalidaVehiculo::class);
    }

    public function resumenesDiarios()
    {
        return $this->hasMany(ResumenVehiculoDiario::class);
    }

    // Relación con mantenimientos
    public function mantenimientos()
    {
        return $this->hasMany(MantenimientoVehiculo::class)->orderByDesc('fecha');
    }
    public function tipoCombustible()
    {
        return $this->belongsTo(TipoCombustible::class);
    }
    public function tipoLubricante()
    {
        return $this->belongsTo(TipoLubricante::class);
    }
    public function tipoRodado()
    {
        return $this->belongsTo(TipoRodado::class);
    }

    public function actas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VehiculoActa::class);
    }
    // Helper para obtener nombre completo
    public function getNombreCompletoAttribute(): string
    {
        $marcaModelo = trim("{$this->marca} {$this->modelo}");

        return $marcaModelo !== ''
            ? "{$this->matricula} - {$marcaModelo}"
            : "{$this->matricula} - {$this->descripcion}";
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'verde'    => 'OK',
            'amarillo' => 'Observación',
            'rojo'     => 'Fuera de servicio',
            'negro'    => 'Dado de baja',
            default    => 'Desconocido',
        };
    }

    public function getEstadoBadgeClassAttribute(): string
    {
        return match ($this->estado) {
            'verde'    => 'badge badge-success',
            'amarillo' => 'badge badge-warning',
            'rojo'     => 'badge badge-danger',
            'negro'    => 'badge badge-dark',
            default    => 'badge badge-secondary',
        };
    }
}
