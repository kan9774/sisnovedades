<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // xxxx_migrate_tipo_combustible_to_catalogo.php
    public function up(): void
    {
        // 1. Precargar los dos valores existentes como filas del catálogo
        $gasOilId = DB::table('tipos_combustible')->insertGetId(['nombre' => 'Gas Oil', 'activo' => true, 'created_at' => now(), 'updated_at' => now()]);
        $naftaId  = DB::table('tipos_combustible')->insertGetId(['nombre' => 'Nafta', 'activo' => true, 'created_at' => now(), 'updated_at' => now()]);

        Schema::table('vehiculos', function (Blueprint $table) {
            $table->foreignId('tipo_combustible_id')->nullable()->after('tipo_vehiculo_id')
                ->constrained('tipos_combustible')->nullOnDelete();
            $table->foreignId('tipo_lubricante_id')->nullable()->after('tipo_combustible_id')
                ->constrained('tipos_lubricante')->nullOnDelete();
            $table->foreignId('tipo_rodado_id')->nullable()->after('tipo_lubricante_id')
                ->constrained('tipos_rodado')->nullOnDelete();
        });

        // 2. Migrar los datos existentes del enum viejo a la FK nueva
        DB::table('vehiculos')->where('tipo_combustible', 'gas_oil')->update(['tipo_combustible_id' => $gasOilId]);
        DB::table('vehiculos')->where('tipo_combustible', 'nafta')->update(['tipo_combustible_id' => $naftaId]);

        // 3. Recién ahora eliminar la columna vieja
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropColumn('tipo_combustible');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->enum('tipo_combustible', ['gas_oil', 'nafta'])->nullable()->after('tipo_vehiculo_id');
        });
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_combustible_id');
            $table->dropConstrainedForeignId('tipo_lubricante_id');
            $table->dropConstrainedForeignId('tipo_rodado_id');
        });
    }
};
