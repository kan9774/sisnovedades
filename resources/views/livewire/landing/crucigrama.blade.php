<div class="crucigrama-panel">

    <div class="crucigrama-panel__estado">
        <span class="crucigrama-estado-texto">
            {{ $completado ? '¡Crucigrama completo!' : 'Completá el crucigrama' }}
        </span>
        <button type="button" wire:click="nuevoJuego" class="sopa-btn">
            <i class="fa-solid fa-rotate-right"></i> Nuevo crucigrama
        </button>
    </div>

    <div class="crucigrama-layout">

        {{-- Tablero --}}
        <div class="crucigrama-tablero" style="grid-template-columns: repeat({{ count($grid[0] ?? []) }}, 1fr);">
            @foreach ($grid as $f => $fila)
                @foreach ($fila as $c => $letra)
                    @php
                        $key = "{$f}-{$c}";
                        $numero = $numeros[$key] ?? null;
                        $correcta = $letra !== null && $this->celdaCorrecta($f, $c);
                    @endphp

                    @if ($letra === null)
                        <div class="crucigrama-celda crucigrama-celda--negra"></div>
                    @else
                        <div class="crucigrama-celda-wrap">
                            @if ($numero)
                                <span class="crucigrama-numero">{{ $numero }}</span>
                            @endif
                            <input
                                type="text"
                                maxlength="1"
                                inputmode="text"
                                autocomplete="off"
                                wire:input="escribir({{ $f }}, {{ $c }}, $event.target.value)"
                                value="{{ $respuestas[$key] ?? '' }}"
                                class="crucigrama-celda {{ $correcta ? 'crucigrama-celda--correcta' : '' }}"
                                {{ $completado ? 'disabled' : '' }}
                            >
                        </div>
                    @endif
                @endforeach
            @endforeach
        </div>

        {{-- Pistas --}}
        <div class="crucigrama-pistas">
            <div class="crucigrama-pistas__grupo">
                <h4>Horizontales</h4>
                <ol>
                    @foreach ($this->across as $entrada)
                        <li><span class="crucigrama-pista-num">{{ $entrada['numero'] }}.</span> {{ $entrada['pista'] }}</li>
                    @endforeach
                </ol>
            </div>

            <div class="crucigrama-pistas__grupo">
                <h4>Verticales</h4>
                <ol>
                    @foreach ($this->down as $entrada)
                        <li><span class="crucigrama-pista-num">{{ $entrada['numero'] }}.</span> {{ $entrada['pista'] }}</li>
                    @endforeach
                </ol>
            </div>
        </div>

    </div>

    @if ($completado)
        <div class="sopa-victoria">
            <div class="sopa-victoria__titulo">¡Misión cumplida!</div>
            <p class="sopa-victoria__texto">Completaste el crucigrama.</p>
            <button type="button" wire:click="nuevoJuego" class="sopa-btn sopa-btn--activo">Jugar otra vez</button>
        </div>
    @endif

</div>