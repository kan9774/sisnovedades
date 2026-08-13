<?php

namespace App\Services\Tetris;

class ScoreEntry
{
    public function __construct(
        public readonly string $name,
        public readonly int $score
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            score: (int) $data['score']
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'score' => $this->score,
        ];
    }

    public function toString(): string
    {
        return sprintf('%s|%d', $this->name, $this->score);
    }

    public static function fromString(string $line): ?self
    {
        $line = trim($line);

        if ($line === '') {
            return null;
        }

        $parts = explode('|', $line);

        if (count($parts) !== 2) {
            return null;
        }

        $name = trim($parts[0]);
        $score = trim($parts[1]);

        if ($name === '' || $score === '') {
            return null;
        }

        $scoreInt = filter_var($score, FILTER_VALIDATE_INT);

        if ($scoreInt === false || $scoreInt < 0) {
            return null;
        }

        return new self($name, (int) $scoreInt);
    }
}
