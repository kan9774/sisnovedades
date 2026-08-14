<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Garantiza que existan los roles y el usuario superadmin
     * base apenas se migra el sistema, sin depender de seeders manuales.
     */
    public function up(): void
    {
        // 1. Rol "visitante" (obligatorio para que el registro público no falle)
        $visitanteId = DB::table('rols')->where('name', 'visitante')->value('id');
        if (!$visitanteId) {
            $visitanteId = DB::table('rols')->insertGetId([
                'name' => 'visitante',
                'description' => 'Solo puede ver guardias cerradas y sus novedades',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Rol "admin" (necesario para el usuario superadmin)
        $adminId = DB::table('rols')->where('name', 'admin')->value('id');
        if (!$adminId) {
            $adminId = DB::table('rols')->insertGetId([
                'name' => 'admin',
                'description' => 'Acceso irrestricto para mantenimiento del sistema',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Usuario SuperAdmin inicial (solo si no existe ninguno todavía)
        $gradoId = \App\Models\Grado::firstOrCreate(['nombre' => 'Sgto.(EC)'])->id;
        $existeSuperAdmin = DB::table('users')->where('is_super_admin', true)->exists();
        if (!$existeSuperAdmin) {
            $userId = DB::table('users')->insertGetId([
                'name' => 'Super',
                'last_name' => 'Admin',
                'grado_id' => $gradoId,
                'email' => 's1bcom1@ejercito.mil.uy',
                'password' => Hash::make('password'),
                'status' => 'active',
                'is_super_admin' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('role_user')->insert([
                'user_id' => $userId,
                'rol_id' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'superadmin@sisnovedades.local')->delete();
        // No borramos los roles por seguridad, podrían tener usuarios asignados.
    }
};
