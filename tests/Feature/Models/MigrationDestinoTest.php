<?php

namespace Tests\Feature\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Tests de la migración change_destino_to_json_in_news.
 *
 * Estos tests NO usan RefreshDatabase (no tiene el trait). Configuran
 * manualmente el estado pre-migración, ejecutan la migración, y verifican
 * el resultado.
 *
 * Cobertura de los bugs:
 * - Bug 1: escalares JSON-válidos ("105") se envuelven en array
 * - Bug 2: multi-destino legacy separado por coma se divide
 */
class MigrationDestinoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // NO usar RefreshDatabase — creamos la tabla manualmente
    }

    private function crearTablaPreMigracion(): void
    {
        $driver = DB::getDriverName();

        // Eliminar tabla si existe (de tests anteriores)
        if ($driver === 'sqlite') {
            DB::statement('DROP TABLE IF EXISTS news');
        } else {
            DB::statement('DROP TABLE IF EXISTS news');
        }

        if ($driver === 'sqlite') {
            DB::statement('CREATE TABLE news (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                guard_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                type TEXT NOT NULL DEFAULT \'Radio\',
                direction TEXT NOT NULL DEFAULT \'Recibido\',
                destino TEXT NULL,
                number TEXT NOT NULL,
                time TEXT NULL,
                office TEXT NULL,
                affair TEXT NULL,
                text TEXT NOT NULL,
                clasification TEXT NOT NULL DEFAULT \'Rutinario\',
                confirmed BOOLEAN NOT NULL DEFAULT 0,
                confirmed_at TIMESTAMP NULL,
                organismo_id INTEGER NULL,
                estado_atencion TEXT NULL,
                tomado_por_id INTEGER NULL,
                tomado_en TIMESTAMP NULL,
                office_id INTEGER NULL,
                deleted_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )');
        } else {
            DB::statement('CREATE TABLE news (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                guard_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                type ENUM(\'Radio\', \'Fax\', \'Correo Electrónico\') NOT NULL DEFAULT \'Radio\',
                direction ENUM(\'Recibido\', \'Expedido\') NOT NULL DEFAULT \'Recibido\',
                destino VARCHAR(255) NULL,
                number VARCHAR(255) NOT NULL,
                time TIME NULL,
                office VARCHAR(255) NULL,
                affair VARCHAR(255) NULL,
                text TEXT NOT NULL,
                clasification ENUM(\'Rutinario\', \'Prioritario\', \'Urgente\', \'Destello\') NOT NULL DEFAULT \'Rutinario\',
                confirmed TINYINT(1) NOT NULL DEFAULT 0,
                confirmed_at TIMESTAMP NULL,
                organismo_id BIGINT UNSIGNED NULL,
                estado_atencion VARCHAR(50) NULL,
                tomado_por_id BIGINT UNSIGNED NULL,
                tomado_en TIMESTAMP NULL,
                office_id BIGINT UNSIGNED NULL,
                deleted_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )');
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('legacyValuesProvider')]
    public function test_migracion_destino_convierte_legacy_correctamente($legacyValue, $expectedArray, $description)
    {
        $this->crearTablaPreMigracion();
        $newsId = 1;

        // Insertar dato legacy como string crudo
        DB::table('news')->insert([
            'id'          => $newsId,
            'guard_id'    => 1,
            'user_id'     => 1,
            'type'        => 'Radio',
            'direction'   => 'Expedido',
            'destino'     => $legacyValue,
            'number'      => '001',
            'text'        => 'Prueba migracion',
            'clasification' => 'Rutinario',
            'office_id'   => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Verificar dato legacy antes de migrar
        $before = DB::table('news')->where('id', $newsId)->value('destino');
        $this->assertSame($legacyValue, $before, "Valor legacy antes de migrar: {$description}");

        // Ejecutar la migración
        $output = Artisan::call('migrate', [
            '--path'    => 'database/migrations/2026_08_18_141214_change_destino_to_json_in_news.php',
            '--force'   => true,
        ]);

        $this->assertSame(0, $output, 'La migración debería ejecutarse sin errores');

        // Verificar resultado post-migración
        $destino = DB::table('news')->where('id', $newsId)->value('destino');
        $decoded = json_decode($destino, true);

        $this->assertSame(JSON_ERROR_NONE, json_last_error(), "JSON decode exitoso: {$description}");
        $this->assertIsArray($decoded, "Resultado es array: {$description}");
        $this->assertEquals($expectedArray, $decoded, "Contenido correcto: {$description}");
    }

    public static function legacyValuesProvider(): array
    {
        return [
            'string simple'           => ['Batallon 1', ['Batallon 1'], 'string simple'],
            'codigo numerico (Bug 1)' => ['105', ['105'], 'codigo numerico — escalar JSON no es array'],
            'multi-destino comas'     => ['Batallon 1, Batallon 2', ['Batallon 1', 'Batallon 2'], 'multi-destino legacy por coma'],
            'array JSON existente'    => [json_encode(['Oficina A', 'Oficina B']), ['Oficina A', 'Oficina B'], 'array JSON existente'],
            'unicode'                 => ['Comando de Log\u00edstica', ['Comando de Log\u00edstica'], 'unicode'],
        ];
    }

    public function test_migracion_destino_multiple_registros_sin_perdida()
    {
        $this->crearTablaPreMigracion();

        // Insertar múltiples registros con datos legacy
        $records = [
            ['id' => 1, 'destino' => 'Batallon 1'],
            ['id' => 2, 'destino' => 'Batallon 2, Batallon 3'],
            ['id' => 3, 'destino' => '105'],
            ['id' => 4, 'destino' => null],
            ['id' => 5, 'destino' => json_encode(['Oficina A', 'Oficina B'])],
        ];

        foreach ($records as $record) {
            DB::table('news')->insert([
                'id'          => $record['id'],
                'guard_id'    => 1,
                'user_id'     => 1,
                'type'        => 'Radio',
                'direction'   => 'Expedido',
                'destino'     => $record['destino'],
                'number'      => (string) $record['id'],
                'text'        => "Prueba {$record['id']}",
                'clasification' => 'Rutinario',
                'office_id'   => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // Contar destinatarios antes (strings legacy)
        $beforeCount = 0;
        foreach ($records as $r) {
            if ($r['destino'] === null) continue;
            if (str_contains($r['destino'], ',')) {
                $beforeCount += count(array_map('trim', explode(',', $r['destino'])));
            } elseif (json_decode($r['destino'], true) && is_array(json_decode($r['destino'], true))) {
                $beforeCount += count(json_decode($r['destino'], true));
            } else {
                $beforeCount += 1;
            }
        }

        // Ejecutar migración
        $output = Artisan::call('migrate', [
            '--path'    => 'database/migrations/2026_08_18_141214_change_destino_to_json_in_news.php',
            '--force'   => true,
        ]);
        $this->assertSame(0, $output);

        // Contar destinatarios después (JSON arrays)
        $afterCount = 0;
        $rows = DB::table('news')->orderBy('id')->get(['id', 'destino']);
        foreach ($rows as $row) {
            if ($row->destino === null) continue;
            $decoded = json_decode($row->destino, true);
            if (is_array($decoded)) {
                $afterCount += count($decoded);
            } else {
                $afterCount += 1;
            }
        }

        // Verificar que no se perdieron destinatarios
        $this->assertSame($beforeCount, $afterCount,
            "Conteo de destinatarios: antes={$beforeCount}, después={$afterCount}");
    }
}
