<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rol;
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
        User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super',
                'last_name' => 'Admin',
                'grade' => 'My.',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'),
                'rol_id' => $rolAdmin->id,
                'is_super_admin' => true,
                'status' => 'active',
            ]
        );

        // También actualizar el admin existente para que sea super admin (opcional)
        $admin = User::where('email', 'admin@example.com')->first();
        if ($admin) {
            $admin->update([
                'is_super_admin' => true,
            ]);
        }

        $this->command->info('Super Admin creado:');
        $this->command->info('Email: superadmin@example.com');
        $this->command->info('Password: password');
    }
}