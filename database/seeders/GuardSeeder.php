<?php

namespace Database\Seeders;

use App\Models\Guard;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GuardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run(): void
    {
        // Buscar usuarios con roles específicos
        $capitan = User::where('email', 'capitan@sistema.com')->first();
        $oficial = User::where('email', 'oficial@sistema.com')->first();
        $escribientes = User::where('email', 'escribiente@sistema.com')->get();

        if (!$capitan || !$oficial || $escribientes->isEmpty()) {
            $this->command->warn('Faltan usuarios. Ejecuta UserSeeder primero.');
            return;
        }

        // Crear guardia para hoy
        $guardia = Guard::create([
            'date' => Carbon::today(),
            'captain_id' => $capitan->id,
            'oficer_id' => $oficial->id,
            'status' => 'open',
            'notes' => 'Guardia de prueba',
        ]);

        // Asignar escribientes (relación muchos a muchos)
        $guardia->escribiente()->attach($escribientes->pluck('id'));

        $this->command->info('Guardia de prueba creada con ID: ' . $guardia->id);
    }
}
