<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gradoIdSgto = \App\Models\Grado::firstOrCreate(['nombre' => 'Sgto.(EC)'])->id;
        $gradoIdCabo = \App\Models\Grado::firstOrCreate(['nombre' => 'Cabo 1°'])->id;
        $gradoIdCap = \App\Models\Grado::firstOrCreate(['nombre' => 'Capitán'])->id;

        $rolMap = Rol::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [$name => (int) $id])->toArray();

        $usuarios = [
            [
                'name' => 'Carlos',
                'last_name' => 'Pereyra',
                'grado_id' => $gradoIdSgto,
                'email' => 'carlos@example.com',
                'rol_name' => 'admin',
            ],
            [
                'name'   => 'Juan',
                'last_name' => 'Perez',
                'grado_id' => $gradoIdCap,
                'email'    => 'capitan@sistema.com',
                'rol_name'   => 'capitan_de_servicio',
            ],
            [
                'name'   => 'Fulano',
                'last_name' => 'Fulanito',
                'grado_id' => $gradoIdCap,
                'email'    => 'capitan2@sistema.com',
                'rol_name'   => 'capitan_de_servicio',
            ],
            [
                'name'   => 'Pedro',
                'last_name' => 'Gomez',
                'grado_id' => $gradoIdSgto,
                'email'    => 'oficial@sistema.com',
                'rol_name'   => 'oficial_de_dia',
            ],
            [
                'name'   => 'Ana',
                'last_name' => 'Lopez',
                'grado_id' => $gradoIdCabo,
                'email'    => 'escribiente@sistema.com',
                'rol_name'   => 'escribiente',
            ],
            [
                'name'   => 'Nadia',
                'last_name' => 'Lopez',
                'grado_id' => $gradoIdCabo,
                'email'    => 'nadía@sistema.com',
                'rol_name'   => 'escribiente',
            ],
        ];
        foreach ($usuarios as $datos) {
            $user = User::firstOrCreate(
                ['email' => $datos['email']],
                [
                    'name' => $datos['name'] ?? $datos['nombre'],
                    'last_name' => $datos['last_name'],
                    'grado_id'  => $datos['grado_id'], 
                    'status' => 'active',
                    'password' => bcrypt('password'),
                ]
            );

            if (isset($rolMap[$datos['rol_name']])) {
                $user->roles()->syncWithoutDetaching($rolMap[$datos['rol_name']]);
            }
        }
    }
}
