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
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // Postgres no castea automáticamente time -> timestamp (no sabe
            // qué fecha asignarle a la parte que falta). Le decimos
            // explícitamente que use la fecha actual como base.
            DB::statement('ALTER TABLE news ALTER COLUMN "time" TYPE timestamp(0) without time zone USING (CURRENT_DATE + "time")');
            DB::statement('ALTER TABLE news ALTER COLUMN "time" DROP NOT NULL');
        } else {
            Schema::table('news', function (Blueprint $table) {
                $table->dateTime('time')->nullable()->change();
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE news ALTER COLUMN "time" TYPE time(0) without time zone USING "time"::time');
            DB::statement('ALTER TABLE news ALTER COLUMN "time" DROP NOT NULL');
        } else {
            Schema::table('news', function (Blueprint $table) {
                $table->time('time')->nullable()->change();
            });
        }
    }
};