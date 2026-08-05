<div id="recreacion" class="recreacion-panel">


    <div class="recreacion-panel__eyebrow">// BCOM1 · SALA DE RECREACIÓN</div>

    @if (!$juego)
        <h2 class="recreacion-panel__titulo">Elegí un juego</h2>

        <div class="recreacion-grid">
            <div class="recreacion-card" wire:click="elegir('sudoku')">
                <div class="recreacion-card__ch">01</div>
                <i class="fa-solid fa-table-cells recreacion-card__icono"></i>
                <div class="recreacion-card__nombre">Sudoku</div>
                <div class="recreacion-card__estado">Disponible</div>
            </div>

            <div class="recreacion-card" wire:click="elegir('sopa')">
                <div class="recreacion-card__ch">02</div>
                <i class="fa-solid fa-magnifying-glass recreacion-card__icono"></i>
                <div class="recreacion-card__nombre">Sopa de letras</div>
                <div class="recreacion-card__estado">Disponible</div>
            </div>
            
            <div class="recreacion-card" wire:click="elegir('crucigrama')">
                <div class="recreacion-card__ch">03</div>
                <i class="fa-solid fa-puzzle-piece recreacion-card__icono"></i>
                <div class="recreacion-card__nombre">Crucigrama</div>
                <div class="recreacion-card__estado">Disponible</div>
            </div>

            <div class="recreacion-card recreacion-card--disabled">
                <div class="recreacion-card__ch">04</div>
                <i class="fa-solid fa-border-none recreacion-card__icono"></i>
                <div class="recreacion-card__nombre">Tetris</div>
                <div class="recreacion-card__estado">Próximamente</div>
            </div>

        </div>
    @else
        <button type="button" class="recreacion-volver" wire:click="volverAlMenu">
            <i class="fa-solid fa-arrow-left"></i> Volver al menú
        </button>

        @if ($juego === 'sudoku')
            <livewire:landing.sudoku-game wire:key="sudoku-activo" />
        @elseif ($juego === 'sopa')
            <livewire:landing.sopa-letras wire:key="sopa-activo" />
        @elseif ($juego === 'crucigrama')
            <livewire:landing.crucigrama wire:key="crucigrama-activo" />
        @endif
    @endif

</div>