<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $permisos = [
            //Permisos de la Guardias
            ['name' => 'crear_guardia', 'description' => 'Permiso para crear usuarios'],
            ['name' => 'cerrar_guardia', 'description' => 'Permiso para cerrar la guardia'],
            ['name' => 'editar_guardia', 'description' => 'Permiso para editar usuarios'],
            ['name' => 'eliminar_guardia', 'description' => 'Permiso para eliminar usuarios'],
            ['name' => 'ver_guardia', 'description' => 'Permiso para ver usuarios'],

            // Permisos para el módulo Palomar
            ['name' => 'ver_palomar', 'description' => 'Ver listados y detalles del palomar'],
            ['name' => 'crear_palomar', 'description' => 'Crear palomares, palomas, estados y vuelos'],
            ['name' => 'editar_palomar', 'description' => 'Editar cualquier entidad del módulo palomar'],
            ['name' => 'eliminar_palomar', 'description' => 'Eliminar entidades del módulo palomar'],


            ['name' => 'registrar_salida', 'description' => 'Permiso para registrar salidas de vehículos'],
            ['name' => 'editar_salida', 'description' => 'Permiso para editar salidas de vehículos'],
            ['name' => 'eliminar_salida', 'description' => 'Permiso para eliminar salidas de vehículos'],

            //Permisos para los Documentos
            ['name' => 'ver_documento', 'description' => 'Ver documentos'],
            ['name' => 'crear_documento', 'description' => 'Subir documentos'],
            ['name' => 'eliminar_documento', 'description' => 'Eliminar documentos'],

            ['name' => 'registrar_novedad', 'description' => 'Permiso para registrar novedades'],
            ['name' => 'registrar_novedad_propia', 'description' => 'Permiso para registrar novedades propias'],
            ['name' => 'ver_novedad', 'description' => 'Permiso para ver novedades'],
            ['name' => 'editar_novedad', 'description' => 'Permiso para editar novedades'],
            ['name' => 'eliminar_novedad', 'description' => 'Permiso para eliminar novedades'],
            ['name' => 'editar_novedad_propia', 'description' => 'Permiso para editar novedades propia'],
            ['name' => 'editar_cualquier_novedad', 'description' => 'Permiso para editar cualquier novedad'],
        ];
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso['name']
            ], $permiso);
        }
    }
}
