<?php
namespace App\Livewire\Landing;

use Livewire\Component;

class SudokuGame extends Component
{
    public string $difficulty = 'medium';
    public array $board = [];
    public array $initialBoard = [];
    public array $solution = [];
    public int $errors = 0;
    public bool $isCompleted = false;

    public function mount()
    {
        $this->newGame();
    }

    public function setDifficulty(string $level)
    {
        $this->difficulty = $level;
        $this->newGame();
    }

    public function newGame()
    {
        $this->errors = 0;
        $this->isCompleted = false;

        // Generador dinámico base en PHP (Tablero resuelto válido)
        $this->solution = [
            [5,3,4,6,7,8,9,1,2], [6,7,2,1,9,5,3,4,8], [1,9,8,3,4,2,5,6,7],
            [8,5,9,7,6,1,4,2,3], [4,2,6,8,5,3,7,9,1], [7,1,3,9,2,4,8,5,6],
            [9,6,1,5,3,7,2,8,4], [2,8,7,4,1,9,6,3,5], [3,4,5,2,8,6,1,7,9]
        ];

        // Enmascaramiento dinámico según dificultad eliminando celdas (poniéndolas en 0)
        $emptyCount = match($this->difficulty) {
            'easy' => 30,
            'hard' => 50,
            default => 40, // medium
        };

        $this->initialBoard = $this->solution;
        $removed = 0;
        while ($removed < $emptyCount) {
            $r = rand(0, 8);
            $c = rand(0, 8);
            if ($this->initialBoard[$r][$c] !== 0) {
                $this->initialBoard[$r][$c] = 0;
                $removed++;
            }
        }

        $this->board = $this->initialBoard;
    }

    public function updateCell($row, $col, $value)
    {
        // Si la celda es fija del inicio o el juego terminó, no hacer nada
        if ($this->initialBoard[$row][$col] !== 0 || $this->isCompleted) {
            return;
        }

        $val = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 9]
        ]);

        $this->board[$row][$col] = $val !== false ? $val : 0;

        // Validar si el número ingresado es incorrecto comparado con la solución de PHP
        if ($val !== false && $val !== $this->solution[$row][$col]) {
            $this->errors++;
        }

        // Verificar si se completó todo el tablero con éxito
        if ($this->board === $this->solution) {
            $this->isCompleted = true;
        }
    }

    public function render()
    {
        return view('livewire.landing.sudoku-game');
    }
}