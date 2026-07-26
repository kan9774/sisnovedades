<?php

namespace App\Exceptions;

use RuntimeException;

class StockInsuficienteException extends RuntimeException
{
    public static function paraItem(string $nombreItem, int $disponible, int $solicitado): self
    {
        return new self(
            "Stock insuficiente de \"{$nombreItem}\": disponible {$disponible}, solicitado {$solicitado}."
        );
    }
}