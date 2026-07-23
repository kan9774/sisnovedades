<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rol_id')->constrained('rols')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'rol_id']);
        });

        // Migrar los roles actuales (users.rol_id) a la tabla pivote antes de borrar la columna.
        DB::table('users')
            ->whereNotNull('rol_id')
            ->orderBy('id')
            ->select('id', 'rol_id')
            ->chunk(200, function ($users) {
                $now = now();
                $rows = $users->map(fn ($user) => [
                    'user_id'    => $user->id,
                    'rol_id'     => $user->rol_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                DB::table('role_user')->insert($rows);
            });

        Schema::table('users', function (Blueprint $table) {
            // Ajustá el nombre si tu FK original no sigue la convención de Laravel.
            $table->dropForeign(['rol_id']);
            $table->dropColumn('rol_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('rol_id')->nullable()->after('grade')->constrained('rols')->nullOnDelete();
        });

        // Restaura como rol_id el primer rol (por id) de cada usuario. Si un usuario
        // tenía más de un rol, el resto se pierde al volver atrás (limitación esperada).
        DB::table('role_user')
            ->select('user_id', DB::raw('MIN(rol_id) as rol_id'))
            ->groupBy('user_id')
            ->orderBy('user_id')
            ->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('users')->where('id', $row->user_id)->update(['rol_id' => $row->rol_id]);
                }
            });

        Schema::dropIfExists('role_user');
    }
};