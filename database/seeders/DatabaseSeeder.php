<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            PermisoSeeder::class,
            RolSeeder::class,
            UserSeeder::class,
            VehiculoConductorSeeder::class,
            SuperAdminSeeder::class,
            GuardSeeder::class,          // ← nuevo
            NovedadesSeeder::class,        // ← nuevo
            SalidaVehiculoSeeder::class,   // ← nuevo
            EstadoPalomaSeeder::class,
            PalomarSeeder::class,
            PalomaSeeder::class,
            VueloSeeder::class,
            CategoriaDocumentoSeeder::class,
        ]);

        $rolAdmin = \App\Models\Rol::where('name', 'admin')->first();
        \App\Models\User::firstOrCreate(
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'grade' => 'Sgto.',
                'last_name' => 'Admin',
                'password' => bcrypt('password'),
                'rol_id' => $rolAdmin->id,
                'status' => 'active',
            ],
        );
    }
}
