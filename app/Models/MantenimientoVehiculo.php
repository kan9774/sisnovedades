<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MantenimientoVehiculo extends Model
{
    use SoftDeletes;

    protected $table = 'mantenimientos_vehiculo';

    protected $fillable = [
        'vehiculo_id',
        'tipo',
        'fecha',
        'kilometraje',
        'descripcion',
        'costo',
        'taller',
        'proximo_mantenimiento_fecha',
        'proximo_mantenimiento_km',
        'registrado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
        'proximo_mantenimiento_fecha' => 'date',
        'kilometraje' => 'integer',
        'proximo_mantenimiento_km' => 'integer',
        'costo' => 'decimal:2',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    // Label legible para el tipo (útil en Blade/DataTables)
    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'preventivo' => 'Preventivo',
            'correctivo' => 'Correctivo',
            'revision_tecnica' => 'Revisión Técnica',
            default => 'Otro',
        };
    }
}