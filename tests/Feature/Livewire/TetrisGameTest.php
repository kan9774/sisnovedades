<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Landing\TetrisGame;
use App\Services\Tetris\ScoreEntry;
use App\Services\Tetris\ScoreManager;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(TetrisGame::class)]
class TetrisGameTest extends TestCase
{
    private string $testScoreFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testScoreFile = storage_path('app/tetris-scores-test.txt');
        if (file_exists($this->testScoreFile)) {
            unlink($this->testScoreFile);
        }

        $this->app->bind(ScoreManager::class, function () {
            return new ScoreManager($this->testScoreFile);
        });
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->testScoreFile)) {
            unlink($this->testScoreFile);
        }
    }

    #[Test]
    public function componente_se_renderiza_correctamente(): void
    {
        Livewire::test(TetrisGame::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.landing.tetris-game');
    }

    #[Test]
    public function menu_principal_se_muestra_al_mount(): void
    {
        Livewire::test(TetrisGame::class)
            ->assertSet('showNameModal', false)
            ->assertSet('gameStarted', false)
            ->assertSet('gameOver', false);
    }

    #[Test]
    public function nombre_de_jugador_es_obligatorio(): void
    {
        Livewire::test(TetrisGame::class)
            ->set('newPlayerName', '')
            ->call('confirmName')
            ->assertHasErrors(['newPlayerName']);
    }

    #[Test]
    public function nombre_vacio_con_espacios_es_invalido(): void
    {
        Livewire::test(TetrisGame::class)
            ->set('newPlayerName', '   ')
            ->call('confirmName')
            ->assertHasErrors(['newPlayerName']);
    }

    #[Test]
    public function nombre_valido_inicia_la_partida(): void
    {
        Livewire::test(TetrisGame::class)
            ->set('newPlayerName', 'Carlos')
            ->call('confirmName')
            ->assertSet('playerName', 'Carlos')
            ->assertSet('showNameModal', false)
            ->assertSet('gameStarted', true)
            ->assertSet('gameOver', false);
    }

    #[Test]
    public function nombre_se_trimsea_automáticamente(): void
    {
        Livewire::test(TetrisGame::class)
            ->set('newPlayerName', '  Carlos  ')
            ->call('confirmName')
            ->assertSet('playerName', 'Carlos');
    }

    #[Test]
    public function nombre_con_saltos_de_linea_se_rechaza(): void
    {
        Livewire::test(TetrisGame::class)
            ->set('newPlayerName', "Carlos\n")
            ->call('confirmName')
            ->assertHasErrors(['newPlayerName']);
    }

    #[Test]
    public function nombre_con_pipe_se_rechaza(): void
    {
        Livewire::test(TetrisGame::class)
            ->set('newPlayerName', 'Car|los')
            ->call('confirmName')
            ->assertHasErrors(['newPlayerName']);
    }

    #[Test]
    public function submit_game_over_con_score_invalido_no_actua(): void
    {
        Livewire::test(TetrisGame::class)
            ->set('playerName', 'Carlos')
            ->set('gameStarted', true)
            ->call('submitGameOver', 'invalid_score', 0, 1)
            ->assertSet('gameOver', false);
    }

    #[Test]
    public function submit_game_over_con_score_valido_guarda_en_ranking(): void
    {
        Livewire::test(TetrisGame::class)
            ->set('playerName', 'Carlos')
            ->set('gameStarted', true)
            ->call('submitGameOver', 12000, 15, 3)
            ->assertSet('gameOver', true)
            ->assertSet('finalScore', 12000)
            ->assertSet('finalLines', 15)
            ->assertSet('finalLevel', 3);

        $this->assertFileExists($this->testScoreFile);

        $manager = new ScoreManager($this->testScoreFile);
        $scores = $manager->getScores();

        $this->assertCount(1, $scores);
        $this->assertEquals('Carlos', $scores[0]->name);
        $this->assertEquals(12000, $scores[0]->score);
    }

    #[Test]
    public function submit_game_over_no_duplica_si_se_llama_dos_veces(): void
    {
        Livewire::test(TetrisGame::class)
            ->set('playerName', 'Carlos')
            ->set('gameStarted', true)
            ->call('submitGameOver', 12000, 15, 3)
            ->call('submitGameOver', 12000, 15, 3);

        $this->assertFileExists($this->testScoreFile);

        $manager = new ScoreManager($this->testScoreFile);
        $scores = $manager->getScores();

        $this->assertCount(1, $scores);
    }

    #[Test]
    public function leaderboard_se_carga_al_mount(): void
    {
        $manager = new ScoreManager($this->testScoreFile);
        $manager->saveScore('Carlos', 12000);
        $manager->saveScore('Ana', 9500);

        $component = Livewire::test(TetrisGame::class);
        $leaderboard = $component->get('leaderboard');

        $this->assertCount(2, $leaderboard);
    }

    #[Test]
    public function leaderboard_se_actualiza_despues_de_guardar_score(): void
    {
        Livewire::test(TetrisGame::class)
            ->set('playerName', 'Carlos')
            ->set('gameStarted', true)
            ->call('submitGameOver', 12000, 15, 3);

        $leaderboard = Livewire::test(TetrisGame::class)
            ->get('leaderboard');

        $this->assertCount(1, $leaderboard);
        $this->assertEquals('Carlos', $leaderboard[0]['name']);
        $this->assertEquals(12000, $leaderboard[0]['score']);
    }

    #[Test]
    public function restart_resetea_el_estado_del_juego(): void
    {
        Livewire::test(TetrisGame::class)
            ->set('playerName', 'Carlos')
            ->set('gameStarted', true)
            ->set('gameOver', true)
            ->set('finalScore', 12000)
            ->call('restart')
            ->assertSet('gameOver', false)
            ->assertSet('finalScore', 0)
            ->assertSet('finalLines', 0)
            ->assertSet('finalLevel', 0);
    }

    #[Test]
    public function toggle_pause_cambia_el_estado_de_pausa(): void
    {
        Livewire::test(TetrisGame::class)
            ->set('playerName', 'Carlos')
            ->set('gameStarted', true)
            ->call('togglePause')
            ->assertSet('isPaused', true)
            ->call('togglePause')
            ->assertSet('isPaused', false);
    }

    #[Test]
    public function nombre_se_sanitiza_con_htmlspecialchars(): void
    {
        Livewire::test(TetrisGame::class)
            ->set('newPlayerName', '<script>alert("xss")</script>')
            ->call('confirmName')
            ->assertSet('playerName', '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;');
    }

    #[Test]
    public function score_demasiado_alto_se_rechaza(): void
    {
        Livewire::test(TetrisGame::class)
            ->set('playerName', 'Carlos')
            ->set('gameStarted', true)
            ->call('submitGameOver', 99999999, 15, 3)
            ->assertSet('gameOver', false);
    }
}
