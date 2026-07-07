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
        $existeSuperAdmin = DB::table('users')->where('is_super_admin', true)->exists();
        if (!$existeSuperAdmin) {
            DB::table('users')->insert([
                'name' => 'Super',
                'last_name' => 'Admin',
                'grade' => 'N/A',
                'email' => 'superadmin@sisnovedades.local',
                'password' => Hash::make('password'),
                'rol_id' => $adminId,
                'status' => 'active',
                'is_super_admin' => true,
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
