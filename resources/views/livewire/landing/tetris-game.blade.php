@assets
<style>
    @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');
</style>
@endassets

<div class="tetris-panel" x-data="tetrisGame(@entangle('gameStarted'), @entangle('gameOver'), @entangle('isPaused'))">
    {{-- Modal de nombre de jugador --}}
    <div x-show="showNameModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="tetris-modal-overlay" @click.self="$wire.set('showNameModal', false)">
        <div class="tetris-modal">
            <div class="tetris-modal__header">
                <i class="fa-solid fa-gamepad tetris-modal__icon"></i>
                <h3 class="tetris-modal__title">TETRIS</h3>
            </div>
            <div class="tetris-modal__body">
                <label for="tetris-player-name" class="tetris-modal__label">Nombre del jugador</label>
                <input type="text" id="tetris-player-name"
                    x-model="$wire.newPlayerName"
                    wire:keydown.enter="confirmName"
                    placeholder="Tu nombre..."
                    class="tetris-modal__input"
                    autofocus
                    maxlength="30">
                @error('newPlayerName')
                    <p class="tetris-modal__error">{{ $message }}</p>
                @enderror
            </div>
            <div class="tetris-modal__footer">
                <button type="button"
                    x-bind:disabled="!$wire.newPlayerName || !$wire.newPlayerName.trim()"
                    wire:click="confirmName"
                    class="tetris-btn tetris-btn--primary tetris-modal__btn">
                    <i class="fa-solid fa-play"></i> Iniciar partida
                </button>
            </div>
        </div>
    </div>

    {{-- HUD --}}
    <div class="tetris-hud" x-show="gameStarted && !gameOver">
        <div class="tetris-hud__player">
            <span class="tetris-hud__label">JUGADOR</span>
            <span class="tetris-hud__value" x-text="$wire.playerName || '---'"></span>
        </div>
        <div class="tetris-hud__score">
            <span class="tetris-hud__label">PUNTOS</span>
            <span class="tetris-hud__value" id="tetris-score-display" x-text="displayScore">0</span>
        </div>
        <div class="tetris-hud__level">
            <span class="tetris-hud__label">NIVEL</span>
            <span class="tetris-hud__value" id="tetris-level-display" x-text="displayLevel">1</span>
        </div>
        <div class="tetris-hud__lines">
            <span class="tetris-hud__label">LINEAS</span>
            <span class="tetris-hud__value" id="tetris-lines-display" x-text="displayLines">0</span>
        </div>
        <button type="button" class="tetris-btn tetris-btn--small"
            x-show="!isPaused"
            wire:click="togglePause">
            <i class="fa-solid fa-pause"></i>
        </button>
        <button type="button" class="tetris-btn tetris-btn--small"
            x-show="isPaused"
            wire:click="togglePause">
            <i class="fa-solid fa-play"></i>
        </button>
    </div>

    {{-- Game Over --}}
    <div class="tetris-gameover-panel" x-show="gameOver" x-transition>
        <div class="tetris-gameover-panel__inner">
            <h3 class="tetris-gameover-panel__title">GAME OVER</h3>
            <div class="tetris-gameover-panel__stats">
                <div class="tetris-gameover-panel__stat">
                    <span class="tetris-gameover-panel__stat-label">PUNTOS</span>
                    <span class="tetris-gameover-panel__stat-value" x-text="$wire.finalScore">0</span>
                </div>
                <div class="tetris-gameover-panel__stat">
                    <span class="tetris-gameover-panel__stat-label">LINEAS</span>
                    <span class="tetris-gameover-panel__stat-value" x-text="$wire.finalLines">0</span>
                </div>
                <div class="tetris-gameover-panel__stat">
                    <span class="tetris-gameover-panel__stat-label">NIVEL</span>
                    <span class="tetris-gameover-panel__stat-value" x-text="$wire.finalLevel">1</span>
                </div>
            </div>
            <button type="button" wire:click="restart"
                class="tetris-btn tetris-btn--primary">
                <i class="fa-solid fa-rotate-right"></i> Reiniciar
            </button>
        </div>
    </div>

    {{-- Area de juego --}}
    <div class="tetris-game-area" x-show="gameStarted && !gameOver">
        <div class="tetris-game-board">
            <canvas id="tetris-canvas" class="tetris-canvas"></canvas>
        </div>

        <div class="tetris-side-panel">
            <div class="tetris-next-piece">
                <span class="tetris-side-panel__label">SIGUIENTE</span>
                <canvas id="tetris-preview" class="tetris-preview-canvas"></canvas>
            </div>

            <div class="tetris-controls">
                <span class="tetris-side-panel__label">CONTROLES</span>
                <div class="tetris-controls-grid">
                    <button type="button" id="tetris-btn-left" class="tetris-control-btn" title="Izquierda">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <button type="button" id="tetris-btn-rotate" class="tetris-control-btn" title="Rotar">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                    <button type="button" id="tetris-btn-right" class="tetris-control-btn" title="Derecha">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                    <button type="button" id="tetris-btn-down" class="tetris-control-btn" title="Bajar">
                        <i class="fa-solid fa-arrow-down"></i>
                    </button>
                    <button type="button" id="tetris-btn-harddrop" class="tetris-control-btn tetris-control-btn--big" title="Hard Drop">
                        <i class="fa-solid fa-arrow-down-long"></i>
                    </button>
                    <button type="button" id="tetris-btn-pause" class="tetris-control-btn" title="Pausa">
                        <i class="fa-solid fa-pause"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Menu principal --}}
    <div class="tetris-menu" x-show="!gameStarted && !showNameModal" x-transition>
        <div class="tetris-menu__inner">
            <i class="fa-solid fa-gamepad tetris-menu__icon"></i>
            <h3 class="tetris-menu__title">TETRIS</h3>
            <p class="tetris-menu__text">Ingresa tu nombre para comenzar</p>
            <button type="button" wire:click="abrirModalNombre"
                class="tetris-btn tetris-btn--primary">
                <i class="fa-solid fa-play"></i> Jugar
            </button>
        </div>
    </div>

    {{-- Ranking Top 10 --}}
    <div class="tetris-leaderboard">
        <h4 class="tetris-leaderboard__title">
            <i class="fa-solid fa-trophy"></i> TOP 10
        </h4>
        <div class="tetris-leaderboard__list" wire:ignore>
            <template x-for="(entry, index) in $wire.leaderboard" :key="index">
                <div class="tetris-leaderboard__row"
                    :class="{ 'tetris-leaderboard__row--first': index === 0 }">
                    <span class="tetris-leaderboard__rank">#<span x-text="index + 1"></span></span>
                    <span class="tetris-leaderboard__name" x-text="entry.name"></span>
                    <span class="tetris-leaderboard__score" x-text="entry.score.toLocaleString()"></span>
                </div>
            </template>
            <template x-if="$wire.leaderboard.length === 0">
                <div class="tetris-leaderboard__empty">Sin puntuaciones todavia</div>
            </template>
        </div>
    </div>

    @script
    <script>
        Alpine.data('tetrisGame', (gameStarted, gameOver, isPaused) => ({
            gameStarted,
            gameOver,
            isPaused,
            showNameModal: @entangle('showNameModal'),
            displayScore: 0,
            displayLines: 0,
            displayLevel: 1,
            tetrisInstance: null,

            async init() {
                const cacheBust = '?v={{ file_exists(public_path("js/tetris/TetrisGame.js")) ? filemtime(public_path("js/tetris/TetrisGame.js")) : time() }}';
                const { TetrisGame: TetrisEngine } = await import('/js/tetris/TetrisGame.js' + cacheBust);
                const { InputHandler } = await import('/js/tetris/input.js' + cacheBust);

                const canvas = document.getElementById('tetris-canvas');
                if (!canvas) return;

                const previewCanvas = document.getElementById('tetris-preview');
                if (previewCanvas) {
                    previewCanvas.width = 10 * 18;
                    previewCanvas.height = 4 * 18;
                }

                const game = new TetrisEngine('tetris-canvas', (score, lines, level) => {
                    this.$wire.submitGameOver(score, lines, level);
                });

                this.tetrisInstance = game;
                this.inputInstance = new InputHandler(game);

                const setupListeners = () => {
                    window.addEventListener('resize', () => {
                        if (this.tetrisInstance) {
                            this.tetrisInstance.resizeCanvas();
                        }
                    });

                    window.addEventListener('tetris-start', () => {
                        if (this.tetrisInstance) {
                            this.tetrisInstance.start();
                            requestAnimationFrame(() => {
                                requestAnimationFrame(() => {
                                    this.tetrisInstance.resizeCanvas();
                                });
                            });
                        }
                    });

                    window.addEventListener('tetris-restart', () => {
                        if (this.tetrisInstance) {
                            this.tetrisInstance.start();
                            requestAnimationFrame(() => {
                                requestAnimationFrame(() => {
                                    this.tetrisInstance.resizeCanvas();
                                });
                            });
                        }
                    });

                    window.addEventListener('tetris-pause', (e) => {
                        if (this.tetrisInstance) {
                            this.tetrisInstance.isPaused = e.detail.paused;
                        }
                    });
                };

                setupListeners();

                try {
                    setTimeout(() => {
                        if (this.tetrisInstance) {
                            this.tetrisInstance.resizeCanvas();
                        }
                    }, 100);
                } catch (e) {
                    console.warn('Tetris: error en resizeCanvas inicial', e);
                }

                this.statsInterval = setInterval(() => {
                    if (this.tetrisInstance && this.tetrisInstance.gameStarted && !this.tetrisInstance.gameOver) {
                        const stats = this.tetrisInstance.getStats();
                        this.displayScore = stats.score;
                        this.displayLines = stats.lines;
                        this.displayLevel = stats.level;
                    }
                }, 100);
            },

            destroy() {
                if (this.inputInstance) {
                    this.inputInstance.destroy();
                }
                if (this.statsInterval) {
                    clearInterval(this.statsInterval);
                }
            }
        }))
    </script>
    @endscript
</div>