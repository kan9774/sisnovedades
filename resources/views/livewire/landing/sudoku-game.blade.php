<div class="sudoku-panel">


    <div class="sudoku-panel__dificultad">
        <button type="button" wire:click="setDifficulty('easy')"
            class="sudoku-btn {{ $difficulty === 'easy' ? 'sudoku-btn--activo' : '' }}">Fácil</button>
        <button type="button" wire:click="setDifficulty('medium')"
            class="sudoku-btn {{ $difficulty === 'medium' ? 'sudoku-btn--activo' : '' }}">Medio</button>
        <button type="button" wire:click="setDifficulty('hard')"
            class="sudoku-btn {{ $difficulty === 'hard' ? 'sudoku-btn--activo' : '' }}">Difícil</button>
    </div>

    <div class="sudoku-panel__estado">
        <span class="sudoku-errores {{ $errors > 0 ? 'sudoku-errores--mal' : 'sudoku-errores--ok' }}">
            Errores: {{ $errors }}
        </span>
        <button type="button" wire:click="newGame" class="sudoku-btn">
            <i class="fa-solid fa-rotate-right"></i> Nuevo juego
        </button>
    </div>

    <table class="sudoku-tablero">
        @foreach ($board as $rowIdx => $row)
            <tr>
                @foreach ($row as $colIdx => $cell)
                    @php
                        $isInitial = $initialBoard[$rowIdx][$colIdx] !== 0;
                        $clasesBorde = [];
                        if ($colIdx % 3 === 2 && $colIdx < 8) $clasesBorde[] = 'sudoku-borde-derecho';
                        if ($rowIdx % 3 === 2 && $rowIdx < 8) $clasesBorde[] = 'sudoku-borde-abajo';
                    @endphp

                    <td class="{{ implode(' ', $clasesBorde) }}">
                        @if ($isInitial)
                            <div class="sudoku-celda-fija">{{ $cell !== 0 ? $cell : '' }}</div>
                        @else
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[1-9]"
                                value="{{ $cell !== 0 ? $cell : '' }}"
                                wire:change="updateCell({{ $rowIdx }}, {{ $colIdx }}, $event.target.value)"
                                class="sudoku-celda-input" autocomplete="off">
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>

    @if ($isCompleted)
        <div class="sudoku-victoria">
            <div class="sudoku-victoria__titulo">¡Completado!</div>
            <p class="sudoku-victoria__texto">Terminaste el Sudoku con {{ $errors }} error{{ $errors === 1 ? '' : 'es' }}.</p>
            <button type="button" wire:click="newGame" class="sudoku-btn sudoku-btn--activo">Jugar otra vez</button>
        </div>
    @endif

</div>