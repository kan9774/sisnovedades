<?php

namespace App\Livewire\Landing;

use Livewire\Component;

class Crucigrama extends Component
{
    // Tamaño del lienzo de trabajo donde se van probando ubicaciones.
    // Se recorta al tamaño real una vez armado el crucigrama.
    public int $tamano = 21;
    public int $cantidadPalabras = 10;

    public array $grid = [];       // [fila][col] => letra real (string) o null (celda negra)
    public array $numeros = [];    // "fila-col" => número de pista
    public array $entradas = [];   // cada palabra colocada: palabra, pista, fila, col, dir, numero
    public array $respuestas = []; // "fila-col" => letra que tipeó el usuario
    public bool $completado = false;

    public function mount(): void
    {
        $this->nuevoJuego();
    }

    public function nuevoJuego(): void
    {
        $this->respuestas = [];
        $this->completado = false;
        $this->generarCrucigrama();
    }

    private function generarCrucigrama(int $intento = 0): void
    {
        $listaPalabras = config('sopa.palabras', []);
        $listaPistas = config('sopa.pistas', []);

        if (!is_array($listaPalabras) || empty($listaPalabras)) {
            throw new \Exception('No hay palabras definidas en config/sopa.php');
        }

        // Solo entran al crucigrama las palabras del banco que además
        // tienen pista cargada en 'pistas', y que entran en el lienzo.
        $banco = [];
        foreach ($listaPalabras as $palabra) {
            $palabra = mb_strtoupper($palabra);
            $pista = $listaPistas[$palabra] ?? ($listaPistas[mb_strtolower($palabra)] ?? null);

            if (!is_string($pista) || trim($pista) === '') {
                continue;
            }

            if (mb_strlen($palabra) < 3 || mb_strlen($palabra) > $this->tamano - 2) {
                continue;
            }

            $banco[$palabra] = $pista;
        }

        if (count($banco) < 6) {
            throw new \Exception('Hacen falta más palabras con pista en config/sopa.php para armar un crucigrama.');
        }

        // Tomamos de más candidatas porque no todas van a lograr cruzarse.
        $candidatas = array_keys($banco);
        shuffle($candidatas);
        $candidatas = array_slice($candidatas, 0, min($this->cantidadPalabras * 4, count($candidatas)));
        usort($candidatas, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        $working = array_fill(0, $this->tamano, array_fill(0, $this->tamano, null));
        $colocadas = [];

        foreach ($candidatas as $palabra) {
            if (count($colocadas) >= $this->cantidadPalabras) {
                break;
            }

            if (empty($colocadas)) {
                // Primera palabra: ancla horizontal, cerca del centro del lienzo.
                $largo = mb_strlen($palabra);
                $fila = intdiv($this->tamano, 2);
                $col = intdiv($this->tamano - $largo, 2);

                $this->colocarEnGrid($working, $palabra, $fila, $col, 'H');
                $colocadas[] = compact('palabra') + ['pista' => $banco[$palabra], 'fila' => $fila, 'col' => $col, 'dir' => 'H'];
                continue;
            }

            $ubicacion = $this->buscarInterseccion($working, $palabra);

            if ($ubicacion !== null) {
                [$fila, $col, $dir] = $ubicacion;
                $this->colocarEnGrid($working, $palabra, $fila, $col, $dir);
                $colocadas[] = compact('palabra') + ['pista' => $banco[$palabra], 'fila' => $fila, 'col' => $col, 'dir' => $dir];
            }
        }

        // Si salió un crucigrama muy pobre (mala suerte con el shuffle), reintentamos.
        if (count($colocadas) < 6 && $intento < 6) {
            $this->generarCrucigrama($intento + 1);
            return;
        }

        [$grid, $colocadas] = $this->recortar($working, $colocadas);

        $this->grid = $grid;
        $this->entradas = $colocadas;
        $this->numerar();
    }

    /**
     * Busca todas las formas válidas de cruzar $palabra con letras ya puestas
     * en $working, y devuelve una al azar entre las que encajan.
     */
    private function buscarInterseccion(array $working, string $palabra): ?array
    {
        $largo = mb_strlen($palabra);
        $candidatos = [];

        for ($i = 0; $i < $largo; $i++) {
            $letra = mb_substr($palabra, $i, 1);

            for ($f = 0; $f < $this->tamano; $f++) {
                for ($c = 0; $c < $this->tamano; $c++) {
                    if ($working[$f][$c] !== $letra) {
                        continue;
                    }

                    foreach (['H', 'V'] as $dir) {
                        $filaIni = $dir === 'V' ? $f - $i : $f;
                        $colIni = $dir === 'H' ? $c - $i : $c;

                        if ($this->encaja($working, $palabra, $filaIni, $colIni, $dir)) {
                            $candidatos[] = [$filaIni, $colIni, $dir];
                        }
                    }
                }
            }
        }

        if (empty($candidatos)) {
            return null;
        }

        return $candidatos[array_rand($candidatos)];
    }

    /**
     * Reglas de un crucigrama válido:
     * - La palabra entra en el lienzo.
     * - No queda pegada a otra palabra en su misma dirección (celda antes/después vacía).
     * - Donde ya hay letra, tiene que coincidir (ahí nace el cruce).
     * - Donde no hay letra, las celdas vecinas perpendiculares deben estar vacías
     *   (si no, la palabra quedaría pegada a otra sin cruzarla realmente).
     * - Tiene que haber al menos un cruce real con lo ya colocado.
     */
    private function encaja(array $working, string $palabra, int $filaIni, int $colIni, string $dir): bool
    {
        $largo = mb_strlen($palabra);
        $dFila = $dir === 'V' ? 1 : 0;
        $dCol = $dir === 'H' ? 1 : 0;

        $filaFin = $filaIni + $dFila * ($largo - 1);
        $colFin = $colIni + $dCol * ($largo - 1);

        if ($filaIni < 0 || $colIni < 0 || $filaFin >= $this->tamano || $colFin >= $this->tamano) {
            return false;
        }

        if ($this->celda($working, $filaIni - $dFila, $colIni - $dCol) !== null) {
            return false;
        }

        if ($this->celda($working, $filaFin + $dFila, $colFin + $dCol) !== null) {
            return false;
        }

        $huboInterseccion = false;

        for ($i = 0; $i < $largo; $i++) {
            $f = $filaIni + $dFila * $i;
            $c = $colIni + $dCol * $i;
            $letra = mb_substr($palabra, $i, 1);
            $actual = $working[$f][$c];

            if ($actual !== null) {
                if ($actual !== $letra) {
                    return false;
                }
                $huboInterseccion = true;
                continue;
            }

            if ($dir === 'H') {
                if ($this->celda($working, $f - 1, $c) !== null) return false;
                if ($this->celda($working, $f + 1, $c) !== null) return false;
            } else {
                if ($this->celda($working, $f, $c - 1) !== null) return false;
                if ($this->celda($working, $f, $c + 1) !== null) return false;
            }
        }

        return $huboInterseccion;
    }

    private function celda(array $working, int $f, int $c): ?string
    {
        if ($f < 0 || $c < 0 || $f >= $this->tamano || $c >= $this->tamano) {
            return 'BORDE'; // tratamos el borde del lienzo como "ocupado"
        }

        return $working[$f][$c];
    }

    private function colocarEnGrid(array &$working, string $palabra, int $fila, int $col, string $dir): void
    {
        $dFila = $dir === 'V' ? 1 : 0;
        $dCol = $dir === 'H' ? 1 : 0;

        for ($i = 0; $i < mb_strlen($palabra); $i++) {
            $working[$fila + $dFila * $i][$col + $dCol * $i] = mb_substr($palabra, $i, 1);
        }
    }

    /**
     * Recorta el lienzo de trabajo (21x21) al rectángulo mínimo que
     * contiene todas las palabras colocadas, y reubica sus coordenadas.
     */
    private function recortar(array $working, array $colocadas): array
    {
        $minF = $this->tamano;
        $maxF = 0;
        $minC = $this->tamano;
        $maxC = 0;

        foreach ($colocadas as $e) {
            $largo = mb_strlen($e['palabra']);
            $finF = $e['dir'] === 'V' ? $e['fila'] + $largo - 1 : $e['fila'];
            $finC = $e['dir'] === 'H' ? $e['col'] + $largo - 1 : $e['col'];

            $minF = min($minF, $e['fila']);
            $maxF = max($maxF, $finF);
            $minC = min($minC, $e['col']);
            $maxC = max($maxC, $finC);
        }

        $grid = [];
        for ($f = $minF; $f <= $maxF; $f++) {
            $filaGrid = [];
            for ($c = $minC; $c <= $maxC; $c++) {
                $filaGrid[] = $working[$f][$c];
            }
            $grid[] = $filaGrid;
        }

        foreach ($colocadas as &$e) {
            $e['fila'] -= $minF;
            $e['col'] -= $minC;
        }

        return [$grid, $colocadas];
    }

    /**
     * Numera las celdas siguiendo la convención estándar de crucigramas:
     * una celda recibe número si empieza una palabra horizontal y/o vertical
     * (recorrido fila por fila, de izquierda a derecha).
     */
    private function numerar(): void
    {
        $this->numeros = [];
        $numero = 1;

        $filas = count($this->grid);
        $cols = $filas > 0 ? count($this->grid[0]) : 0;

        for ($f = 0; $f < $filas; $f++) {
            for ($c = 0; $c < $cols; $c++) {
                if ($this->grid[$f][$c] === null) {
                    continue;
                }

                $iniciaH = ($c === 0 || $this->grid[$f][$c - 1] === null)
                    && ($c + 1 < $cols && $this->grid[$f][$c + 1] !== null);

                $iniciaV = ($f === 0 || $this->grid[$f - 1][$c] === null)
                    && ($f + 1 < $filas && $this->grid[$f + 1][$c] !== null);

                if ($iniciaH || $iniciaV) {
                    $this->numeros["{$f}-{$c}"] = $numero;
                    $numero++;
                }
            }
        }

        foreach ($this->entradas as &$e) {
            $e['numero'] = $this->numeros["{$e['fila']}-{$e['col']}"] ?? null;
        }
    }

    public function escribir(int $fila, int $col, string $letra): void
    {
        if ($this->completado) {
            return;
        }

        $letra = mb_strtoupper(mb_substr($letra, -1));
        $this->respuestas["{$fila}-{$col}"] = $letra;

        $this->verificarCompleto();
    }

    private function verificarCompleto(): void
    {
        foreach ($this->grid as $f => $filaGrid) {
            foreach ($filaGrid as $c => $letraReal) {
                if ($letraReal === null) {
                    continue;
                }

                if (($this->respuestas["{$f}-{$c}"] ?? null) !== $letraReal) {
                    return;
                }
            }
        }

        $this->completado = true;
    }

    public function celdaCorrecta(int $fila, int $col): bool
    {
        $real = $this->grid[$fila][$col] ?? null;

        return $real !== null && ($this->respuestas["{$fila}-{$col}"] ?? null) === $real;
    }

    public function getAcrossProperty(): array
    {
        return collect($this->entradas)->where('dir', 'H')->sortBy('numero')->values()->all();
    }

    public function getDownProperty(): array
    {
        return collect($this->entradas)->where('dir', 'V')->sortBy('numero')->values()->all();
    }

    public function render()
    {
        return view('livewire.landing.crucigrama');
    }
}