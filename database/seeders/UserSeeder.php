<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $roles = \App\Models\Rol::pluck('id', 'name');
        $gradoIdSgto = \App\Models\Grado::firstOrCreate(['nombre' => 'Sgto.(EC)'])->id;
        $gradoIdCabo = \App\Models\Grado::firstOrCreate(['nombre' => 'Cabo 1°'])->id;
        $gradoIdCap = \App\Models\Grado::firstOrCreate(['nombre' => 'Capitán'])->id;

        $usuarios = [
            [
                'name' => 'Carlos',
                'last_name' => 'Pereyra',
                'grado_id' => $gradoIdSgto,
                'email' => 'carlos@example.com',
                'rol_id' => $roles['admin'],
            ],
            [
                'name'   => 'Juan',
                'last_name' => 'Perez',
                'grado_id' => $gradoIdCap,
                'email'    => 'capitan@sistema.com',
                'rol_id'   => $roles['capitan_de_servicio'],
            ],
            [
                'name'   => 'Fulano',
                'last_name' => 'Fulanito',
                'grado_id' => $gradoIdCap,
                'email'    => 'capitan2@sistema.com',
                'rol_id'   => $roles['capitan_de_servicio'],
            ],
            [
                'name'   => 'Pedro',
                'last_name' => 'Gomez',
                'grado_id' => $gradoIdSgto,
                'email'    => 'oficial@sistema.com',
                'rol_id'   => $roles['oficial_de_dia'],
            ],
            [
                'name'   => 'Ana',
                'last_name' => 'Lopez',
                'grado_id' => $gradoIdCabo,
                'email'    => 'escribiente@sistema.com',
                'rol_id'   => $roles['escribiente'],
            ],
            [
                'name'   => 'Nadia',
                'last_name' => 'Lopez',
                'grado_id' => $gradoIdCabo,
                'email'    => 'escribiente@sistema.com',
                'rol_id'   => $roles['escribiente'],
            ],
        ];
        foreach ($usuarios as $datos) {
            \App\Models\User::firstOrCreate(
                ['email' => $datos['email']],
                [
                    'name' => $datos['name'] ?? $datos['nombre'],
                    'last_name' => $datos['last_name'],
                    'grado_id'  => $datos['grado_id'], 
                    'status' => 'active', // Cambia el estado según tus necesidades
                    'rol_id' => $datos['rol_id'],
                    'password' => bcrypt('password'), // Cambia la contraseña según tus necesidades
                ]
            );
        }
    }
}
