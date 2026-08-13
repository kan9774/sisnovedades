<?php

namespace App\Services\Tetris;

use Illuminate\Support\Facades\Log;

class ScoreManager
{
    private string $filePath;

    public function __construct(?string $filePath = null)
    {
        $this->filePath = $filePath ?? storage_path('app/tetris-scores.txt');
    }

    /**
     * Obtener el Top 10 de puntuaciones.
     *
     * @return array<ScoreEntry>
     */
    public function getScores(): array
    {
        if (!file_exists($this->filePath)) {
            $this->createFile();
            return [];
        }

        $contents = file_get_contents($this->filePath);

        if ($contents === false || $contents === '') {
            return [];
        }

        $lines = array_filter(explode("\n", $contents), fn ($line) => trim($line) !== '');

        $entries = [];

        foreach ($lines as $line) {
            $entry = ScoreEntry::fromString($line);
            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $this->sortAndTruncate($entries);
    }

    /**
     * Guardar una nueva puntuación y retornar el Top 10 actualizado.
     *
     * @return array<ScoreEntry>
     */
    public function saveScore(string $name, int $score): array
    {
        $entry = $this->validateAndCreateEntry($name, $score);

        $scores = $this->getScores();
        $scores[] = $entry;

        $scores = $this->sortAndTruncate($scores);

        $this->writeFile($scores);

        return $scores;
    }

    /**
     * Validar y crear un ScoreEntry.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateAndCreateEntry(string $name, int $score): ScoreEntry
    {
        if (str_contains($name, "\n") || str_contains($name, "\r") || str_contains($name, "|")) {
            throw new \InvalidArgumentException('El nombre contiene caracteres inválidos.');
        }

        $name = trim($name);

        if (mb_strlen($name) === 0) {
            throw new \InvalidArgumentException('El nombre del jugador no puede estar vacío.');
        }

        if (mb_strlen($name) > 30) {
            throw new \InvalidArgumentException('El nombre no puede exceder los 30 caracteres.');
        }

        if (str_contains($name, "\n") || str_contains($name, "\r") || str_contains($name, "|")) {
            throw new \InvalidArgumentException('El nombre contiene caracteres inválidos.');
        }

        if ($score < 0) {
            throw new \InvalidArgumentException('La puntuación no puede ser negativa.');
        }

        if ($score > 999999) {
            throw new \InvalidArgumentException('La puntuación excede el límite máximo.');
        }

        return new ScoreEntry(htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), $score);
    }

    /**
     * Ordenar por puntuación descendente y resolver empates por nombre.
     *
     * @param array<ScoreEntry> $entries
     * @return array<ScoreEntry>
     */
    protected function sortAndTruncate(array $entries): array
    {
        usort($entries, function (ScoreEntry $a, ScoreEntry $b): int {
            if ($b->score !== $a->score) {
                return $b->score <=> $a->score;
            }
            return strcasecmp($a->name, $b->name);
        });

        return array_slice($entries, 0, 10, true);
    }

    /**
     * Escribir el archivo de puntuaciones.
     *
     * @param array<ScoreEntry> $scores
     */
    protected function writeFile(array $scores): void
    {
        $content = implode("\n", array_map(fn (ScoreEntry $e) => $e->toString(), $scores));
        $content .= "\n";

        $this->createFile();

        $fp = fopen($this->filePath, 'w');

        if ($fp === false) {
            Log::error('Tetris ScoreManager: No se pudo abrir el archivo de puntuaciones.', ['path' => $this->filePath]);
            throw new \RuntimeException('No se pudo abrir el archivo de puntuaciones para escritura.');
        }

        flock($fp, LOCK_EX);
        $written = fwrite($fp, $content);
        flock($fp, LOCK_UN);
        fclose($fp);

        if ($written === false || $written === 0) {
            Log::error('Tetris ScoreManager: Error al escribir el archivo de puntuaciones.', ['path' => $this->filePath]);
            throw new \RuntimeException('No se pudo escribir el archivo de puntuaciones.');
        }
    }

    protected function createFile(): void
    {
        $dir = dirname($this->filePath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($this->filePath)) {
            touch($this->filePath);
            chmod($this->filePath, 0666);
        }
    }
}
