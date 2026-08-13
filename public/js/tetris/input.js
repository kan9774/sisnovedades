export class InputHandler {
    constructor(game) {
        this.game = game;
        this.keys = {};
        this.touchControls = {};
        this.touchStartY = 0;
        this.touchStartX = 0;
        this.isTouching = false;

        this.setupKeyboard();
        this.setupTouch();
    }

    setupKeyboard() {
        this._keydownHandler = (e) => {
            if (this.game.isPaused || this.game.gameOver) {
                if (e.key === 'p' || e.key === 'P') {
                    this.game.togglePause();
                }
                return;
            }

            switch (e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    this.game.moveLeft();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    this.game.moveRight();
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    this.game.softDrop();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.game.rotate();
                    break;
                case ' ':
                    e.preventDefault();
                    this.game.hardDrop();
                    break;
                case 'p':
                case 'P':
                    this.game.togglePause();
                    break;
            }
        };

        this._keyupHandler = (e) => {
            this.keys[e.key] = false;
        };

        window.addEventListener('keydown', this._keydownHandler);
        window.addEventListener('keyup', this._keyupHandler);
    }

    setupTouch() {
        const canvas = document.getElementById('tetris-canvas');
        if (!canvas) return;

        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            const touch = e.touches[0];
            this.touchStartX = touch.clientX;
            this.touchStartY = touch.clientY;
            this.isTouching = true;
        }, { passive: false });

        canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            if (!this.isTouching || this.game.isPaused || this.game.gameOver) return;

            const touch = e.touches[0];
            const dx = touch.clientX - this.touchStartX;
            const dy = touch.clientY - this.touchStartY;

            if (Math.abs(dx) > 30) {
                if (dx > 0) {
                    this.game.moveRight();
                } else {
                    this.game.moveLeft();
                }
                this.touchStartX = touch.clientX;
            }

            if (dy > 30) {
                this.game.softDrop();
                this.touchStartY = touch.clientY;
            }
        }, { passive: false });

        canvas.addEventListener('touchend', (e) => {
            e.preventDefault();
            this.isTouching = false;
        }, { passive: false });

        const setupBtn = (id, action) => {
            const btn = document.getElementById(id);
            if (!btn) return;

            btn.addEventListener('touchstart', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (!this.game.isPaused && !this.game.gameOver) {
                    action();
                }
            }, { passive: false });

            btn.addEventListener('mousedown', (e) => {
                e.preventDefault();
                if (!this.game.isPaused && !this.game.gameOver) {
                    action();
                }
            });
        };

        setupBtn('tetris-btn-left', () => this.game.moveLeft());
        setupBtn('tetris-btn-right', () => this.game.moveRight());
        setupBtn('tetris-btn-down', () => this.game.softDrop());
        setupBtn('tetris-btn-rotate', () => this.game.rotate());
        setupBtn('tetris-btn-harddrop', () => this.game.hardDrop());
        setupBtn('tetris-btn-pause', () => this.game.togglePause());
    }

    destroy() {
        window.removeEventListener('keydown', this._keydownHandler);
        window.removeEventListener('keyup', this._keyupHandler);
    }
}