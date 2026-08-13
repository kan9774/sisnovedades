import { PIECES, PIECE_NAMES, randomPieceName, getPieceMatrix, rotateMatrix } from './pieces.js';

const BOARD_WIDTH = 10;
const BOARD_HEIGHT = 20;
const CELL_SIZE = 30;

const LINE_SCORES = {
    1: 100,
    2: 300,
    3: 500,
    4: 800
};

export class TetrisGame {
    constructor(canvasId, onGameOver) {
        this.canvasId = canvasId;
        this.onGameOver = onGameOver;
        this.canvas = document.getElementById(canvasId);
        this.ctx = this.canvas.getContext('2d');

        this.board = this.createBoard();
        this.score = 0;
        this.lines = 0;
        this.level = 1;
        this.gameOver = false;
        this.isPaused = false;
        this.gameStarted = false;

        this.currentPiece = null;
        this.currentMatrix = null;
        this.currentName = null;
        this.pieceX = 0;
        this.pieceY = 0;

        this.nextPieceName = null;
        this.previewCellSize = 18;

        this.dropInterval = 1000;
        this.lastDrop = 0;
        this.animationId = null;

        this.lineClearRows = [];
        this.isLineClearing = false;
        this.lineClearTimer = 0;

        this.setupCanvas();
    }

    createBoard() {
        return Array.from({ length: BOARD_HEIGHT }, () =>
            Array(BOARD_WIDTH).fill(null)
        );
    }

    setupCanvas() {
        const ctx = this.ctx;
        ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
    }

    resizeCanvas() {
        const container = this.canvas.parentElement;
        const rawWidth = container.clientWidth - 32;

        if (rawWidth <= 0) {
            return;
        }

        const maxWidth = Math.min(rawWidth, 320);
        const cellSize = Math.floor(maxWidth / BOARD_WIDTH);
        const canvasHeight = BOARD_HEIGHT * cellSize + 100;

        this.canvas.width = BOARD_WIDTH * cellSize;
        this.canvas.height = canvasHeight;
        this.cellSize = cellSize;
        this.previewCellSize = Math.max(12, Math.floor(cellSize * 0.6));

        this.draw();
    }

    start() {
        if (this.gameStarted) return;
        this.gameStarted = true;
        this.gameOver = false;
        this.isPaused = false;
        this.board = this.createBoard();
        this.score = 0;
        this.lines = 0;
        this.level = 1;
        this.dropInterval = 1000;
        this.nextPieceName = randomPieceName();
        this.spawnPiece();
        this.lastDrop = performance.now();
        this.animationId = requestAnimationFrame((t) => this.gameLoop(t));
    }

    spawnPiece() {
        this.currentName = this.nextPieceName;
        this.nextPieceName = randomPieceName();
        this.currentMatrix = getPieceMatrix(this.currentName);
        this.pieceX = Math.floor((BOARD_WIDTH - this.currentMatrix[0].length) / 2);
        this.pieceY = 0;

        if (this.collides(this.pieceX, this.pieceY, this.currentMatrix)) {
            this.endGame();
        }
    }

    gameLoop(timestamp) {
        if (this.gameOver) return;

        if (!this.isPaused && !this.isLineClearing) {
            if (timestamp - this.lastDrop > this.dropInterval) {
                this.drop();
                this.lastDrop = timestamp;
            }
        }

        this.draw();
        this.animationId = requestAnimationFrame((t) => this.gameLoop(t));
    }

    drop() {
        if (!this.collides(this.pieceX, this.pieceY + 1, this.currentMatrix)) {
            this.pieceY++;
        } else {
            this.lockPiece();
        }
    }

    softDrop() {
        if (this.gameOver || this.isPaused || this.isLineClearing) return;
        if (!this.collides(this.pieceX, this.pieceY + 1, this.currentMatrix)) {
            this.pieceY++;
            this.score += 1;
        }
    }

    hardDrop() {
        if (this.gameOver || this.isPaused || this.isLineClearing) return;
        let dropDistance = 0;
        while (!this.collides(this.pieceX, this.pieceY + 1, this.currentMatrix)) {
            this.pieceY++;
            dropDistance++;
        }
        this.score += dropDistance * 2;
        this.lockPiece();
    }

    moveLeft() {
        if (this.gameOver || this.isPaused || this.isLineClearing) return;
        if (!this.collides(this.pieceX - 1, this.pieceY, this.currentMatrix)) {
            this.pieceX--;
        }
    }

    moveRight() {
        if (this.gameOver || this.isPaused || this.isLineClearing) return;
        if (!this.collides(this.pieceX + 1, this.pieceY, this.currentMatrix)) {
            this.pieceX++;
        }
    }

    rotate() {
        if (this.gameOver || this.isPaused || this.isLineClearing) return;
        const rotated = rotateMatrix(this.currentMatrix);
        if (!this.collides(this.pieceX, this.pieceY, rotated)) {
            this.currentMatrix = rotated;
        } else {
            const kicks = [-1, 1, -2, 2];
            for (const kick of kicks) {
                if (!this.collides(this.pieceX + kick, this.pieceY, rotated)) {
                    this.pieceX += kick;
                    this.currentMatrix = rotated;
                    break;
                }
            }
        }
    }

    collides(px, py, matrix) {
        for (let r = 0; r < matrix.length; r++) {
            for (let c = 0; c < matrix[r].length; c++) {
                if (matrix[r][c]) {
                    const boardX = px + c;
                    const boardY = py + r;
                    if (boardX < 0 || boardX >= BOARD_WIDTH || boardY >= BOARD_HEIGHT) {
                        return true;
                    }
                    if (boardY >= 0 && this.board[boardY][boardX]) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    lockPiece() {
        for (let r = 0; r < this.currentMatrix.length; r++) {
            for (let c = 0; c < this.currentMatrix[r].length; c++) {
                if (this.currentMatrix[r][c]) {
                    const boardY = this.pieceY + r;
                    const boardX = this.pieceX + c;
                    if (boardY >= 0 && boardY < BOARD_HEIGHT && boardX >= 0 && boardX < BOARD_WIDTH) {
                        this.board[boardY][boardX] = {
                            color: PIECES[this.currentName].color,
                            glow: PIECES[this.currentName].glow
                        };
                    }
                }
            }
        }

        this.clearLines();
        this.spawnPiece();
    }

    clearLines() {
        const cleared = [];
        for (let r = BOARD_HEIGHT - 1; r >= 0; r--) {
            if (this.board[r].every(cell => cell !== null)) {
                cleared.push(r);
            }
        }

        if (cleared.length > 0) {
            this.isLineClearing = true;
            this.lineClearRows = cleared;
            this.lineClearTimer = performance.now();

            setTimeout(() => {
                for (const row of cleared.sort((a, b) => a - b)) {
                    this.board.splice(row, 1);
                    this.board.unshift(Array(BOARD_WIDTH).fill(null));
                }

                const numLines = cleared.length;
                this.lines += numLines;
                this.score += (LINE_SCORES[numLines] || 0) * this.level;

                const newLevel = Math.floor(this.lines / 10) + 1;
                if (newLevel !== this.level) {
                    this.level = newLevel;
                    this.dropInterval = Math.max(100, 1000 - (this.level - 1) * 80);
                }

                this.isLineClearing = false;
                this.lineClearRows = [];
            }, 350);
        }
    }

    draw() {
        const ctx = this.ctx;
        const cellSize = this.cellSize;
        const offsetX = 0;
        const offsetY = 0;
        const previewY = BOARD_HEIGHT * cellSize + 15;

        ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        drawBoard(ctx, this.board, cellSize, offsetX, offsetY);

        if (this.currentMatrix && !this.gameOver) {
            drawGhostPiece(ctx, this.currentMatrix, this.pieceX, this.pieceY, this.board, cellSize, offsetX, offsetY);
            drawPiece(ctx, this.currentMatrix, offsetX + this.pieceX * cellSize, offsetY + this.pieceY * cellSize, cellSize,
                PIECES[this.currentName].color, PIECES[this.currentName].glow);
        }

        if (this.isLineClearing) {
            drawLineClearEffect(ctx, this.lineClearRows, cellSize, offsetX, offsetY);
        }

        if (this.isPaused && !this.gameOver) {
            drawPausedOverlay(ctx, this.canvas);
        }

        if (this.gameOver) {
            drawGameOverOverlay(ctx, this.canvas);
        }

        if (this.nextPieceName) {
            drawNextPiece(ctx, this.nextPieceName, this.previewCellSize, offsetX, previewY);

            ctx.fillStyle = '#8C9AA8';
            ctx.font = `${Math.max(9, cellSize * 0.35)}px "IBM Plex Mono", monospace`;
            ctx.textAlign = 'left';
            ctx.textBaseline = 'top';
            ctx.fillText('SIGUIENTE', offsetX, previewY - 2);
        }
    }

    togglePause() {
        if (this.gameOver || !this.gameStarted) return;
        this.isPaused = !this.isPaused;
    }

    endGame() {
        this.gameOver = true;
        this.gameStarted = false;
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
        this.draw();
        if (this.onGameOver) {
            this.onGameOver(this.score, this.lines, this.level);
        }
    }

    getStats() {
        return {
            score: this.score,
            lines: this.lines,
            level: this.level
        };
    }
}

import { drawBoard, drawPiece, drawGhostPiece, drawNextPiece, drawLineClearEffect, drawPausedOverlay, drawGameOverOverlay } from './renderer.js';