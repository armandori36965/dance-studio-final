import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from 'tailwindcss'; // <-- 新增這一行

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', // <-- 我們將在這裡引入 Tailwind
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(), // <-- 新增這一行
    ],
});