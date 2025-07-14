import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', // 我們將改用這個檔案
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});