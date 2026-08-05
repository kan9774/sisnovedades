<?php

namespace App\Livewire\Landing;

use Livewire\Component;

class SopaLetras extends Component
{
    public int $filas = 15;
    public int $columnas = 15;

    public array $grid = [];
    public array $palabras = [];
    public array $celdasEncontradas = [];
    public ?array $inicio = null;
    public bool $completado = false;
    public int $cantidadPalabras = 8;

    protected array $direcciones = [
        [0, 1],   // derecha
        [0, -1],  // izquierda
        [1, 0],   // abajo
        [-1, 0],  // arriba
        [1, 1],   // diagonal abajo-derecha
        [-1, -1], // diagonal arriba-izquierda
        [1, -1],  // diagonal abajo-izquierda
        [-1, 1],  // diagonal arriba-derecha
    ];

    public function mount(): void
    {
        $this->nuevoJuego();
    }

    public function nuevoJuego(): void
    {
        $this->inicio = null;
        $this->celdasEncontradas = [];
        $this->completado = false;

        // Obtiene el banco completo desde config/sopa.php
        $bancoPalabras = config('sopa.palabras', []);

        // Asegurar que es un array
        if (!is_array($bancoPalabras) || empty($bancoPalabras)) {
            throw new \Exception('No hay palabras definidas en config/sopa.php');
        }

        // Normalizar: asegurar índices numéricos y mayúsculas
        $bancoPalabras = array_values(array_map('strtoupper', $bancoPalabras));
        $bancoPalabras = array_unique($bancoPalabras);

        // Seleccionar palabras aleatorias
        $cantidad = min($this->cantidadPalabras, count($bancoPalabras));
        
        // Si solo hay 1 palabra, array_rand devuelve un entero
        if ($cantidad === 1) {
            $palabrasSeleccionadas = [$bancoPalabras[array_rand($bancoPalabras)]];
        } else {
            $indicesAleatorios = array_rand($bancoPalabras, $cantidad);
            $palabrasSeleccionadas = [];
            foreach ($indicesAleatorios as $indice) {
                $palabrasSeleccionadas[] = $bancoPalabras[$indice];
            }
        }

        $this->palabras = array_fill_keys($palabrasSeleccionadas, false);
        
        $this->generarGrid();
    }

    private function generarGrid(): void
    {
        $grid = array_fill(0, $this->filas, array_fill(0, $this->columnas, null));

        $palabrasKeys = array_keys($this->palabras);
        shuffle($palabrasKeys);

        // Colocar primero las palabras más largas: son las que más cuesta
        // encajar, y conviene intentarlo cuando la grilla todavía está vacía.
        usort($palabrasKeys, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        $palabrasColocadas = [];

        foreach ($palabrasKeys as $palabra) {
            $colocada = false;
            $intentos = 0;
            $largo = mb_strlen($palabra);

            while (!$colocada && $intentos < 1000) {
                $intentos++;

                $dir = $this->direcciones[array_rand($this->direcciones)];
                $dFila = $dir[0];
                $dCol = $dir[1];

                $filaIni = rand(0, $this->filas - 1);
                $colIni = rand(0, $this->columnas - 1);

                $filaFin = $filaIni + ($dFila * ($largo - 1));
                $colFin = $colIni + ($dCol * ($largo - 1));

                if ($filaFin < 0 || $filaFin >= $this->filas || $colFin < 0 || $colFin >= $this->columnas) {
                    continue;
                }

                $encaja = true;
                $celdasTemp = [];

                for ($i = 0; $i < $largo; $i++) {
                    $f = $filaIni + ($dFila * $i);
                    $c = $colIni + ($dCol * $i);
                    $letra = mb_substr($palabra, $i, 1);

                    if ($grid[$f][$c] !== null && $grid[$f][$c] !== $letra) {
                        $encaja = false;
                        break;
                    }

                    $celdasTemp[] = [$f, $c, $letra];
                }

                if ($encaja) {
                    foreach ($celdasTemp as [$f, $c, $letra]) {
                        $grid[$f][$c] = $letra;
                    }
                    $colocada = true;
                }
            }

            // Si tras todos los intentos no se pudo colocar, la palabra NO
            // entra en la lista final: así el listado de "palabras a buscar"
            // siempre coincide exactamente con lo que hay en la grilla.
            if ($colocada) {
                $palabrasColocadas[$palabra] = false;
            }
        }

        // Reemplazamos $this->palabras solo por las que realmente se
        // colocaron. Si alguna quedó afuera, se pierde una palabra del
        // banco, pero nunca aparece una palabra fantasma que no está en la grilla.
        $this->palabras = $palabrasColocadas;

        $alfabeto = range('A', 'Z');
        for ($f = 0; $f < $this->filas; $f++) {
            for ($c = 0; $c < $this->columnas; $c++) {
                if ($grid[$f][$c] === null) {
                    $grid[$f][$c] = $alfabeto[array_rand($alfabeto)];
                }
            }
        }

        $this->grid = $grid;
    }

    public function seleccionar(int $fila, int $col): void
    {
        if ($this->completado) {
            return;
        }

        if ($fila < 0 || $fila >= $this->filas || $col < 0 || $col >= $this->columnas) {
            $this->inicio = null;
            return;
        }

        if ($this->inicio === null) {
            $this->inicio = [$fila, $col];
            return;
        }

        [$fi, $ci] = $this->inicio;

        if ($fi === $fila && $ci === $col) {
            $this->inicio = null;
            return;
        }

        $dRow = $fila - $fi;
        $dCol = $col - $ci;
        $esLinea = $dRow === 0 || $dCol === 0 || abs($dRow) === abs($dCol);

        if (!$esLinea) {
            $this->inicio = null;
            return;
        }

        $pasos = max(abs($dRow), abs($dCol));
        $stepRow = $dRow === 0 ? 0 : ($dRow > 0 ? 1 : -1);
        $stepCol = $dCol === 0 ? 0 : ($dCol > 0 ? 1 : -1);

        $celdas = [];
        $letras = '';

        for ($i = 0; $i <= $pasos; $i++) {
            $f = $fi + $stepRow * $i;
            $c = $ci + $stepCol * $i;
            
            if ($f < 0 || $f >= $this->filas || $c < 0 || $c >= $this->columnas) {
                $this->inicio = null;
                return;
            }
            
            $celdas[] = [$f, $c];
            $letras .= $this->grid[$f][$c];
        }

        $letrasInvertidas = strrev($letras);

        foreach ($this->palabras as $palabra => $encontrada) {
            if ($encontrada) {
                continue;
            }

            if ($palabra === $letras || $palabra === $letrasInvertidas) {
                $this->palabras[$palabra] = true;
                
                foreach ($celdas as [$f, $c]) {
                    $this->celdasEncontradas["{$f}-{$c}"] = true;
                }
                
                break;
            }
        }

        $this->inicio = null;

        if (!in_array(false, $this->palabras, true)) {
            $this->completado = true;
        }
    }

    public function celdaEncontrada(int $fila, int $col): bool
    {
        return isset($this->celdasEncontradas["{$fila}-{$col}"]);
    }

    public function getProgreso(): array
    {
        $total = count($this->palabras);
        $encontradas = 0;
        
        foreach ($this->palabras as $encontrada) {
            if ($encontrada) {
                $encontradas++;
            }
        }
        
        return [
            'encontradas' => $encontradas,
            'total' => $total,
            'porcentaje' => $total > 0 ? round(($encontradas / $total) * 100) : 0
        ];
    }

    public function render()
    {
        return view('livewire.landing.sopa-letras');
    }
}