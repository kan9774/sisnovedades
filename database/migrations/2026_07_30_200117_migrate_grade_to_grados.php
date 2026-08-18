<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Un registro de catálogo por cada valor distinto que ya existe en users.grade
        $valoresExistentes = DB::table('users')
            ->whereNotNull('grade')
            ->where('grade', '!=', '')
            ->distinct()
            ->pluck('grade');

        $mapaGrados = [];
        foreach ($valoresExistentes as $nombre) {
            $mapaGrados[$nombre] = DB::table('grados')->insertGetId([
                'nombre'     => $nombre,
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('grado_id')->nullable()->after('grade')
                ->constrained('grados')->nullOnDelete();
        });

        // 2. Migrar los usuarios existentes a la FK nueva
        foreach ($mapaGrados as $nombre => $id) {
            DB::table('users')->where('grade', $nombre)->update(['grado_id' => $id]);
        }

        // 3. Recién ahora eliminar la columna vieja
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('grade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('grade')->nullable()->after('grado_id');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('UPDATE users SET grade = grados.nombre FROM grados WHERE users.grado_id = grados.id');
        } else {
            DB::table('users')
                ->join('grados', 'users.grado_id', '=', 'grados.id')
                ->update(['users.grade' => DB::raw('grados.nombre')]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('grado_id');
        });
    }
};