<?php

namespace Tests\Unit\Services\Tetris;

use App\Services\Tetris\ScoreEntry;
use App\Services\Tetris\ScoreManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(ScoreManager::class)]
#[CoversClass(ScoreEntry::class)]
class ScoreManagerTest extends TestCase
{
    private string $testFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testFile = storage_path('app/tetris-scores-test.txt');
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }

    #[Test]
    public function archivo_se_crea_automáticamente(): void
    {
        $manager = new ScoreManager($this->testFile);
        $manager->getScores();

        $this->assertFileExists($this->testFile);
    }

    #[Test]
    public function archivo_vacio_retorna_array_vacio(): void
    {
        $manager = new ScoreManager($this->testFile);
        $manager->createFile ?? $manager->getScores();

        $fp = fopen($this->testFile, 'w');
        fclose($fp);

        $scores = $manager->getScores();
        $this->assertIsArray($scores);
        $this->assertEmpty($scores);
    }

    #[Test]
    public function puede_leer_y_parsear_registros_existentes(): void
    {
        $manager = new ScoreManager($this->testFile);

        $fp = fopen($this->testFile, 'w');
        fwrite($fp, "Carlos|12000\nAna|9500\nPedro|8000\n");
        fclose($fp);

        $scores = $manager->getScores();

        $this->assertCount(3, $scores);
        $this->assertEquals('Carlos', $scores[0]->name);
        $this->assertEquals(12000, $scores[0]->score);
        $this->assertEquals('Ana', $scores[1]->name);
        $this->assertEquals(9500, $scores[1]->score);
    }

    #[Test]
    public function puede_guardar_una_nueva_puntuacion(): void
    {
        $manager = new ScoreManager($this->testFile);

        $scores = $manager->saveScore('Carlos', 12000);

        $this->assertCount(1, $scores);
        $this->assertEquals('Carlos', $scores[0]->name);
        $this->assertEquals(12000, $scores[0]->score);
        $this->assertFileExists($this->testFile);
    }

    #[Test]
    public function las_puntuaciones_se_ordenan_descendentemente(): void
    {
        $manager = new ScoreManager($this->testFile);

        $manager->saveScore('Pedro', 5000);
        $manager->saveScore('Carlos', 12000);
        $manager->saveScore('Ana', 9500);

        $scores = $manager->getScores();

        $this->assertEquals(12000, $scores[0]->score);
        $this->assertEquals(9500, $scores[1]->score);
        $this->assertEquals(5000, $scores[2]->score);
    }

    #[Test]
    public function solo_conserva_los_10_mejores_registros(): void
    {
        $manager = new ScoreManager($this->testFile);

        for ($i = 0; $i < 15; $i++) {
            $score = (15 - $i) * 1000;
            $manager->saveScore("Jugador{$i}", $score);
        }

        $scores = $manager->getScores();

        $this->assertCount(10, $scores);
        $this->assertEquals(15000, $scores[0]->score);
        $this->assertEquals(6000, $scores[9]->score);
    }

    #[Test]
    public function empates_se_resuelven_por_nombre_alfabeticamente(): void
    {
        $manager = new ScoreManager($this->testFile);

        $manager->saveScore('Zoe', 5000);
        $manager->saveScore('Ana', 5000);
        $manager->saveScore('Carlos', 5000);

        $scores = $manager->getScores();

        $this->assertEquals('Ana', $scores[0]->name);
        $this->assertEquals('Carlos', $scores[1]->name);
        $this->assertEquals('Zoe', $scores[2]->name);
    }

    #[Test]
    public function nombre_vacio_lanza_excepcion(): void
    {
        $manager = new ScoreManager($this->testFile);

        $this->expectException(\InvalidArgumentException::class);
        $manager->saveScore('', 1000);
    }

    #[Test]
    public function nombre_con_saltos_de_linea_lanza_excepcion(): void
    {
        $manager = new ScoreManager($this->testFile);

        $this->expectException(\InvalidArgumentException::class);
        $manager->saveScore("Carlos\n", 1000);
    }

    #[Test]
    public function nombre_con_pipe_lanza_excepcion(): void
    {
        $manager = new ScoreManager($this->testFile);

        $this->expectException(\InvalidArgumentException::class);
        $manager->saveScore('Car|los', 1000);
    }

    #[Test]
    public function nombre_muy_largo_lanza_excepcion(): void
    {
        $manager = new ScoreManager($this->testFile);

        $this->expectException(\InvalidArgumentException::class);
        $manager->saveScore(str_repeat('A', 31), 1000);
    }

    #[Test]
    public function puntuacion_negativa_lanza_excepcion(): void
    {
        $manager = new ScoreManager($this->testFile);

        $this->expectException(\InvalidArgumentException::class);
        $manager->saveScore('Carlos', -100);
    }

    #[Test]
    public function puntuacion_demasiado_alta_lanza_excepcion(): void
    {
        $manager = new ScoreManager($this->testFile);

        $this->expectException(\InvalidArgumentException::class);
        $manager->saveScore('Carlos', 9999999);
    }

    #[Test]
    public function registros_invalidos_se_ignoran_al_leer(): void
    {
        $manager = new ScoreManager($this->testFile);

        $fp = fopen($this->testFile, 'w');
        fwrite($fp, "Carlos|12000\ninvalid_line\n|5000\nAna|\n\nPedro|8000\n");
        fclose($fp);

        $scores = $manager->getScores();

        $this->assertCount(2, $scores);
        $this->assertEquals('Carlos', $scores[0]->name);
        $this->assertEquals('Pedro', $scores[1]->name);
    }

    #[Test]
    public function scoreentry_to_string_format_correcto(): void
    {
        $entry = new ScoreEntry('Carlos', 12000);
        $this->assertEquals('Carlos|12000', $entry->toString());
    }

    #[Test]
    public function scoreentry_from_string_parsa_correctamente(): void
    {
        $entry = ScoreEntry::fromString('Carlos|12000');

        $this->assertNotNull($entry);
        $this->assertEquals('Carlos', $entry->name);
        $this->assertEquals(12000, $entry->score);
    }

    #[Test]
    public function scoreentry_from_string_rechaza_lineas_invalidas(): void
    {
        $this->assertNull(ScoreEntry::fromString(''));
        $this->assertNull(ScoreEntry::fromString('invalid'));
        $this->assertNull(ScoreEntry::fromString('Carlos|-100'));
        $this->assertNull(ScoreEntry::fromString('|5000'));
    }
}
