<?php

namespace Database\Seeders;

use App\Models\Unidad;
use App\Models\UnidadModulo;
use Illuminate\Database\Seeder;

/**
 * Reproduce EXACTAMENTE el comportamiento previo a la introducción de
 * listas curadas: todas las unidades activas en cada módulo, excepto
 * C.A.C.O. en los tres lugares donde la exclusión estaba hardcodeada
 * (register.blade.php, vehiculos/index.blade.php y query de unidadesTabs).
 *
 * Idempotente (firstOrCreate): puede re-correrse sin duplicar filas.
 */
class UnidadModuloSeeder extends Seeder
{
    /**
     * Módulos donde C.A.C.O. NO debe aparecer (exclusión que antes vivía
     * hardcodeada por nombre en el código). En el resto SÍ se incluye.
     */
    private const SIN_CACO = [
        'usuarios_registro',
        'vehiculos_form',
        'vehiculos_tabs',
    ];

    public function run(): void
    {
        $activas = Unidad::where('activo', true)->orderBy('nombre')->get();

        foreach (UnidadModulo::MODULOS as $modulo) {
            foreach ($activas as $unidad) {
                if (in_array($modulo, self::SIN_CACO, true) && $unidad->nombre === 'C.A.C.O.') {
                    continue;
                }

                UnidadModulo::firstOrCreate([
                    'unidad_id' => $unidad->id,
                    'modulo' => $modulo,
                ]);
            }
        }
    }
}
