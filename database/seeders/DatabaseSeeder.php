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
            UnidadSeeder::class,
            UnidadModuloSeeder::class,
            DepartamentosSeeder::class,
            TipoApoyoSeeder::class,
        ]);

        $rolAdmin = \App\Models\Rol::where('name', 'admin')->first();
        $gradoId = \App\Models\Grado::firstOrCreate(['nombre' => 'Sgto.(EC)'])->id;
        $adminUser = \App\Models\User::firstOrCreate(
            [
                'email' => 'admin@example.com',
            ],
            [
                'name' => 'Admin User',
                'last_name' => 'Admin',
                'grado_id' => $gradoId,
                'password' => bcrypt('password'),
                'status' => 'active',
            ],
        );

        if ($rolAdmin && $adminUser) {
            $adminUser->roles()->syncWithoutDetaching($rolAdmin->id);
        }

    }
}
