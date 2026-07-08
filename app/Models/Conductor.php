<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Conductor extends Model
{
    use SoftDeletes, LogsActivity;

        public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Conductores'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }

    protected $fillable = [
        'grado',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'documento',
        'nro_licencia',
        'categoria_licencia',
        'fecha_vencimiento_licencia',
        'lugar_carne_salud',
        'fecha_vencimiento_carne_salud',
        'lugar_carne_habilitante',
        'numero_carne_habilitante',
        'fecha_vencimiento_carne_habilitante',
        'tipo_vehiculo_habilitado',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'fecha_vencimiento_licencia' => 'date',
        'fecha_vencimiento_carne_salud' => 'date',
        'fecha_vencimiento_carne_habilitante' => 'date',
        'activo' => 'boolean',
    ];

    protected $table = 'conductores';

    // Helpers
    public function getNombreCompletoAttribute(): string
    {
        $parts = array_filter([
            $this->grado,
            $this->primer_nombre,
            $this->segundo_nombre,
            $this->primer_apellido,
            $this->segundo_apellido,
        ]);
        return implode(' ', $parts);
    }

    public function getNombreCortoAttribute(): string
    {
        $iniciales = strtoupper(
            substr($this->primer_nombre, 0, 1) . 
            substr($this->primer_apellido, 0, 1)
        );
        return "{$this->grado} {$iniciales}.";
    }

    public function getNombreVisibleAttribute(): string
    {
        $parts = array_filter([
            $this->grado,
            $this->primer_nombre,
            $this->primer_apellido,
        ]);
        return implode(' ', $parts);
    }

    public function getLicenciaVigenteAttribute(): bool
    {
        return $this->fecha_vencimiento_licencia >= today();
    }

    public function getCarneSaludVigenteAttribute(): bool
    {
        return $this->fecha_vencimiento_carne_salud && 
               $this->fecha_vencimiento_carne_salud >= today();
    }

    public function getCarneHabilitanteVigenteAttribute(): bool
    {
        return $this->fecha_vencimiento_carne_habilitante && 
               $this->fecha_vencimiento_carne_habilitante >= today();
    }

}