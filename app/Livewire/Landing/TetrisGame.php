<?php

namespace App\Livewire\Landing;

use App\Services\Tetris\ScoreManager;
use Livewire\Attributes\Validate;
use Livewire\Component;

class TetrisGame extends Component
{
    public ?string $playerName = null;
    public bool $showNameModal = false;
    public bool $gameOver = false;
    public int $finalScore = 0;
    public int $finalLines = 0;
    public int $finalLevel = 0;
    public bool $gameStarted = false;
    public bool $isPaused = false;

    /** @var array<array{name: string, score: int}> */
    public array $leaderboard = [];

    #[Validate('required|string|min:1|max:30')]
    public string $newPlayerName = '';

    public function mount()
    {
        $this->loadLeaderboard();
    }

    public function loadLeaderboard(): void
    {
        $scores = app(ScoreManager::class)->getScores();
        $this->leaderboard = array_map(fn ($e) => $e->toArray(), $scores);
    }

    public function confirmName(): void
    {
        $rawName = $this->newPlayerName;

        if (str_contains($rawName, "\n") || str_contains($rawName, "\r") || str_contains($rawName, '|')) {
            $this->addError('newPlayerName', 'El nombre contiene caracteres inválidos.');
            return;
        }

        $this->validate();

        $name = trim($this->newPlayerName);

        if (mb_strlen($name) === 0) {
            $this->addError('newPlayerName', 'El nombre no puede estar vacío.');
            return;
        }

        $this->playerName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $this->showNameModal = false;
        $this->gameStarted = true;
        $this->gameOver = false;

        $this->dispatch('tetris-start');
    }

    public function submitGameOver($score, $lines, $level): void
    {
        if (!$this->gameStarted || $this->gameOver) {
            return;
        }

        $score = filter_var($score, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 999999]]);

        if ($score === false) {
            return;
        }

        $this->gameOver = true;
        $this->finalScore = (int) $score;
        $this->finalLines = filter_var($lines, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) ?: 0;
        $this->finalLevel = filter_var($level, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;

        try {
            $scores = app(ScoreManager::class)->saveScore($this->playerName, $this->finalScore);
            $this->leaderboard = array_map(fn ($e) => $e->toArray(), $scores);
        } catch (\Throwable $e) {
            report($e);
        }

        $this->dispatch('tetris-gameover');
    }

    public function restart(): void
    {
        $this->gameOver = false;
        $this->finalScore = 0;
        $this->finalLines = 0;
        $this->finalLevel = 0;
        $this->isPaused = false;

        $this->dispatch('tetris-restart');
    }

    public function togglePause(): void
    {
        if (!$this->gameStarted || $this->gameOver) {
            return;
        }

        $this->isPaused = !$this->isPaused;
        $this->dispatch('tetris-pause', paused: $this->isPaused);
    }

    public function abrirModalNombre(): void
    {
        $this->showNameModal = true;
        $this->newPlayerName = '';
        $this->resetErrorBag('newPlayerName');
    }

    public function render()
    {
        return view('livewire.landing.tetris-game');
    }
}
