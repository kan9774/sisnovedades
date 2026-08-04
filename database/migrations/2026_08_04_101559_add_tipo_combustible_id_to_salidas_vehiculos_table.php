<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salidas_vehiculos', function (Blueprint $table) {
            $table->foreignId('tipo_combustible_id')
                ->nullable()
                ->after('tipo_combustible')
                ->constrained('tipos_combustible')
                ->nullOnDelete();
        });

        // Garantizar que existan las filas del catálogo para los valores viejos
        $gasOilId = DB::table('tipos_combustible')->where('nombre', 'Gas Oil')->value('id')
            ?? DB::table('tipos_combustible')->insertGetId([
                'nombre' => 'Gas Oil', 'activo' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);

        $naftaId = DB::table('tipos_combustible')->where('nombre', 'Nafta')->value('id')
            ?? DB::table('tipos_combustible')->insertGetId([
                'nombre' => 'Nafta', 'activo' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);

        DB::table('salidas_vehiculos')->where('tipo_combustible', 'gas_oil')->update(['tipo_combustible_id' => $gasOilId]);
        DB::table('salidas_vehiculos')->where('tipo_combustible', 'nafta')->update(['tipo_combustible_id' => $naftaId]);
    }

    public function down(): void
    {
        Schema::table('salidas_vehiculos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_combustible_id');
        });
    }
};