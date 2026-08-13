import { defineConfig, normalizePath } from 'vite';
import laravel from 'laravel-vite-plugin';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        viteStaticCopy({
            targets: [
                {
                    src: 'resources/js/tetris/*',
                    dest: normalizePath(path.resolve(__dirname, 'public/js/tetris')),
                    rename: { stripBase: true },
                },
            ],
            watch: {
                reloadPageOnChange: true,
            },
        }),
    ],
});