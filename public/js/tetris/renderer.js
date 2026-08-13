import { PIECES } from './pieces.js';

const BOARD_BG = '#0d1117';
const GRID_COLOR = 'rgba(255, 255, 255, 0.04)';
const EMPTY_CELL = '#161b22';

function drawCell(ctx, x, y, size, color, glow) {
    ctx.fillStyle = color;
    ctx.fillRect(x + 1, y + 1, size - 2, size - 2);

    if (glow) {
        ctx.shadowColor = glow;
        ctx.shadowBlur = 6;
        ctx.fillRect(x + 1, y + 1, size - 2, size - 2);
        ctx.shadowBlur = 0;
    }

    ctx.fillStyle = 'rgba(255, 255, 255, 0.12)';
    ctx.fillRect(x + 1, y + 1, size - 2, 2);
    ctx.fillRect(x + 1, y + 1, 2, size - 2);

    ctx.fillStyle = 'rgba(0, 0, 0, 0.2)';
    ctx.fillRect(x + 1, y + size - 3, size - 2, 2);
    ctx.fillRect(x + size - 3, y + 1, 2, size - 2);
}

function drawGhostCell(ctx, x, y, size) {
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
    ctx.lineWidth = 1;
    ctx.strokeRect(x + 2, y + 2, size - 4, size - 4);
}

export function drawBoard(ctx, board, cellSize, offsetX, offsetY) {
    ctx.fillStyle = BOARD_BG;
    ctx.fillRect(offsetX, offsetY, board[0].length * cellSize, board.length * cellSize);

    for (let r = 0; r < board.length; r++) {
        for (let c = 0; c < board[0].length; c++) {
            const x = offsetX + c * cellSize;
            const y = offsetY + r * cellSize;

            if (board[r][c]) {
                drawCell(ctx, x, y, cellSize, board[r][c].color, board[r][c].glow);
            } else {
                ctx.fillStyle = EMPTY_CELL;
                ctx.fillRect(x + 1, y + 1, cellSize - 2, cellSize - 2);
                ctx.strokeStyle = GRID_COLOR;
                ctx.lineWidth = 0.5;
                ctx.strokeRect(x + 1, y + 1, cellSize - 2, cellSize - 2);
            }
        }
    }
}

export function drawPiece(ctx, matrix, offsetX, offsetY, cellSize, color, glow) {
    for (let r = 0; r < matrix.length; r++) {
        for (let c = 0; c < matrix[r].length; c++) {
            if (matrix[r][c]) {
                const x = offsetX + c * cellSize;
                const y = offsetY + r * cellSize;
                drawCell(ctx, x, y, cellSize, color, glow);
            }
        }
    }
}

export function drawGhostPiece(ctx, matrix, pieceX, pieceY, board, cellSize, offsetX, offsetY) {
    let ghostRow = pieceY;
    while (!collidesWithBoard(matrix, pieceX, ghostRow + 1, board)) {
        ghostRow++;
    }

    if (ghostRow !== pieceY) {
        for (let r = 0; r < matrix.length; r++) {
            for (let c = 0; c < matrix[r].length; c++) {
                if (matrix[r][c]) {
                    const x = offsetX + (pieceX + c) * cellSize;
                    const y = offsetY + (ghostRow + r) * cellSize;
                    drawGhostCell(ctx, x, y, cellSize);
                }
            }
        }
    }
}

function collidesWithBoard(matrix, px, py, board) {
    for (let r = 0; r < matrix.length; r++) {
        for (let c = 0; c < matrix[r].length; c++) {
            if (matrix[r][c]) {
                const boardX = px + c;
                const boardY = py + r;
                if (boardX < 0 || boardX >= 10 || boardY >= board.length) {
                    return true;
                }
                if (boardY >= 0 && board[boardY][boardX]) {
                    return true;
                }
            }
        }
    }
    return false;
}

export function drawNextPiece(ctx, pieceName, cellSize, x, y) {
    if (!pieceName) return;
    const piece = PIECES[pieceName];
    if (!piece) return;
    const matrix = piece.shape;
    const color = piece.color;
    const glow = piece.glow;

    const previewSize = cellSize * 0.7;
    const matrixW = matrix[0].length * previewSize;
    const matrixH = matrix.length * previewSize;

    for (let r = 0; r < matrix.length; r++) {
        for (let c = 0; c < matrix[r].length; c++) {
            if (matrix[r][c]) {
                const px = x + c * previewSize + (10 * previewSize - matrixW) / 2;
                const py = y + r * previewSize + (4 * previewSize - matrixH) / 2;
                drawCell(ctx, px, py, previewSize, color, glow);
            }
        }
    }
}

export function drawLineClearEffect(ctx, clearedRows, cellSize, offsetX, offsetY, maxAlpha = 0.8) {
    const time = Date.now() % 400;
    const alpha = maxAlpha * (1 - time / 400);

    for (const row of clearedRows) {
        const y = offsetY + row * cellSize;
        ctx.fillStyle = `rgba(255, 255, 255, ${alpha})`;
        ctx.fillRect(offsetX, y, 10 * cellSize, cellSize);
    }
}

export function drawPausedOverlay(ctx, canvas) {
    ctx.fillStyle = 'rgba(0, 0, 0, 0.6)';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = '#00f3ff';
    ctx.font = 'bold 24px "Press Start 2P", monospace';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('PAUSA', canvas.width / 2, canvas.height / 2);
}

export function drawGameOverOverlay(ctx, canvas) {
    ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = '#ff4d4d';
    ctx.font = 'bold 20px "Press Start 2P", monospace';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('GAME OVER', canvas.width / 2, canvas.height / 2 - 20);

    ctx.fillStyle = '#E8ECEF';
    ctx.font = '12px "Press Start 2P", monospace';
    ctx.fillText('Presiona Reiniciar', canvas.width / 2, canvas.height / 2 + 20);
}
