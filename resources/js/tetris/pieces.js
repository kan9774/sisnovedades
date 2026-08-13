export const PIECES = {
    I: {
        shape: [
            [0, 0, 0, 0],
            [1, 1, 1, 1],
            [0, 0, 0, 0],
            [0, 0, 0, 0]
        ],
        color: '#00f3ff',
        glow: 'rgba(0, 243, 255, 0.6)'
    },
    O: {
        shape: [
            [1, 1],
            [1, 1]
        ],
        color: '#ffe600',
        glow: 'rgba(255, 230, 0, 0.6)'
    },
    T: {
        shape: [
            [0, 1, 0],
            [1, 1, 1],
            [0, 0, 0]
        ],
        color: '#b44aff',
        glow: 'rgba(180, 74, 255, 0.6)'
    },
    S: {
        shape: [
            [0, 1, 1],
            [1, 1, 0],
            [0, 0, 0]
        ],
        color: '#39ff14',
        glow: 'rgba(57, 255, 20, 0.6)'
    },
    Z: {
        shape: [
            [1, 1, 0],
            [0, 1, 1],
            [0, 0, 0]
        ],
        color: '#ff4d4d',
        glow: 'rgba(255, 77, 77, 0.6)'
    },
    J: {
        shape: [
            [1, 0, 0],
            [1, 1, 1],
            [0, 0, 0]
        ],
        color: '#4a90ff',
        glow: 'rgba(74, 144, 255, 0.6)'
    },
    L: {
        shape: [
            [0, 0, 1],
            [1, 1, 1],
            [0, 0, 0]
        ],
        color: '#ff8c00',
        glow: 'rgba(255, 140, 0, 0.6)'
    }
};

export const PIECE_NAMES = Object.keys(PIECES);

export function randomPieceName() {
    const idx = Math.floor(Math.random() * PIECE_NAMES.length);
    return PIECE_NAMES[idx];
}

export function getPieceMatrix(name) {
    return PIECES[name].shape.map(row => [...row]);
}

export function getPieceColor(name) {
    return PIECES[name].color;
}

export function getPieceGlow(name) {
    return PIECES[name].glow;
}

export function rotateMatrix(matrix) {
    const N = matrix.length;
    const rotated = Array.from({ length: N }, () => Array(N).fill(0));
    for (let r = 0; r < N; r++) {
        for (let c = 0; c < N; c++) {
            rotated[c][N - 1 - r] = matrix[r][c];
        }
    }
    return rotated;
}
