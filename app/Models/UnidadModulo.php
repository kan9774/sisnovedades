<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnidadModulo extends Model
{
    protected $table = 'unidad_modulo';

    /**
     * Claves de módulo válidas para listas curadas de unidades.
     * Cada clave identifica un selector del sistema; el contenido de la
     * lista se gestiona vía tabla pivot unidad_modulo (ver UnidadModuloSeeder).
     */
    public const MODULOS = [
        'usuarios_alta',
        'usuarios_edicion',
        'usuarios_registro',
        'vehiculos_form',
        'vehiculos_tabs',
        'guardias_rancho',
        'apoyos_asignacion',
        'pase',
        'comision',
    ];

    /**
     * Etiquetas legibles para cada clave de módulo (pantalla de administración
     * "Unidades por Módulo" y cualquier selector futuro que las muestre).
     * Debe mantenerse en paridad con MODULOS.
     */
    public const ETIQUETAS = [
        'usuarios_alta' => 'Alta de Usuario',
        'usuarios_edicion' => 'Edición de Usuario',
        'usuarios_registro' => 'Registro de Acceso',
        'vehiculos_form' => 'Formulario de Vehículos',
        'vehiculos_tabs' => 'Pestañas de Vehículos',
        'guardias_rancho' => 'Rancho de Guardia',
        'apoyos_asignacion' => 'Asignación de Apoyos',
        'pase' => 'Pases de Personal',
        'comision' => 'Comisiones',
    ];

    protected $fillable = [
        'unidad_id',
        'modulo',
    ];

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }
}
