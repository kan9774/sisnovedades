<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar el rol admin
        $rolAdmin = Rol::where('name', 'admin')->first();

        // Si no existe el rol admin, crearlo
        if (!$rolAdmin) {
            $rolAdmin = Rol::create([
                'name' => 'admin',
                'description' => 'Administrador del sistema',
            ]);
        }

        // Crear o actualizar el Super Admin
        $gradoId = \App\Models\Grado::firstOrCreate(['nombre' => 'Sgto.(EC)'])->id;
        $user = User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super',
                'last_name' => 'Admin',
                'grado_id' => $gradoId,
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
                'status' => 'active',
            ]
        );

        // Asignar rol admin vía pivot table
        $user->roles()->syncWithoutDetaching($rolAdmin->id);

        // También actualizar el admin existente para que sea super admin (opcional)
        $admin = User::where('email', 'admin@example.com')->first();
        if ($admin) {
            $admin->update([
                'is_super_admin' => true,
            ]);
            $admin->roles()->syncWithoutDetaching($rolAdmin->id);
        }

        $this->command->info('Super Admin creado:');
        $this->command->info('Email: superadmin@example.com');
        $this->command->info('Password: password');
    }
}