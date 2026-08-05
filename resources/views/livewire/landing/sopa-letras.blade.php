<div class="sopa-panel">

    <div class="sopa-panel__estado">
        <span class="sopa-palabras-contador">
            Encontradas: {{ collect($palabras)->filter()->count() }} / {{ count($palabras) }}
        </span>
        <button type="button" wire:click="nuevoJuego" class="sopa-btn">
            <i class="fa-solid fa-rotate-right"></i> Nuevo juego
        </button>
    </div>
    {{-- Lista de palabras a buscar --}}
    <div class="sopa-lista-palabras">
        @foreach ($palabras as $palabra => $encontrada)
            <span class="sopa-palabra-item {{ $encontrada ? 'sopa-palabra-item--tachada' : '' }}">
                {{ $palabra }}
            </span>
        @endforeach
    </div>

    {{-- Tablero de la Sopa de Letras --}}
    <div class="sopa-tablero" style="grid-template-columns: repeat({{ $columnas }}, 1fr);">
        @foreach ($grid as $rowIdx => $row)
            @foreach ($row as $colIdx => $cell)
                @php
                    $key = "{$rowIdx}-{$colIdx}";
                    $esInicio = $inicio && $inicio[0] === $rowIdx && $inicio[1] === $colIdx;
                    $esEncontrada = isset($celdasEncontradas[$key]);
                @endphp
                <button type="button"
                    wire:click="seleccionar({{ $rowIdx }}, {{ $colIdx }})"
                    class="sopa-celda 
                        {{ $esInicio ? 'sopa-celda--inicio' : '' }} 
                        {{ $esEncontrada ? 'sopa-celda--encontrada' : '' }}">
                    {{ $cell }}
                </button>
            @endforeach
        @endforeach
    </div>

    @if ($completado)
        <div class="sopa-victoria">
            <div class="sopa-victoria__titulo">¡Misión cumplida!</div>
            <p class="sopa-victoria__texto">Has encontrado todas las palabras en la red de comunicaciones.</p>
            <button type="button" wire:click="nuevoJuego" class="sopa-btn sopa-btn--activo">Jugar otra vez</button>
        </div>
    @endif

</div>