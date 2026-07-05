<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Rol;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'escribiente' => [
                'description' => 'Registra novedades dentro de su guardia asignada',
                'permisos'    => ['registrar_novedad', 'editar_novedad_propia'],
            ],
            'oficial_de_dia' => [
                'description' => 'Abre la guardia y supervisa el registro de novedades',
                'permisos'    => ['crear_guardia', 'registrar_novedad', 'editar_novedad_propia', 'editar_cualquier_novedad', 'cerrar_guardia'],
            ],
            'capitan_de_servicio' => [
                'description' => 'Supervisor responsable. Cierra la guardia y tiene permisos totales',
                'permisos'    => ['crear_guardia', 'cerrar_guardia', 'registrar_novedad', 'editar_novedad_propia', 'editar_cualquier_novedad', 'eliminar_novedad'],
            ],
            'visitante' => [
                'description' => 'Solo puede ver guardias cerradas y sus novedades',
                'permisos'    => [], // sin permisos de acción
            ],
            'admin' => [
                'description' => 'Acceso irrestricto para mantenimiento del sistema',
                'permisos'    => [], // se asignan todos abajo
            ],
            'colombofilo' => [
                'description' => 'Encargado del palomar militar',
                'permisos'    => ['ver_palomar', 'crear_palomar', 'editar_palomar', 'eliminar_palomar'],
            ],
        ];

        $todosLosPermisos = Permission::pluck('id')->toArray();

        foreach ($roles as $nombre => $datos) {
            $rol = Rol::firstOrCreate(
                ['name' => $nombre],
                ['description' => $datos['description']]
            );

            if ($nombre === 'admin') {
                $rol->permisos()->sync($todosLosPermisos);
            } else {
                $ids = Permission::whereIn('name', $datos['permisos'])->pluck('id')->toArray();
                $rol->permisos()->sync($ids);
            }
        }
    }
}
