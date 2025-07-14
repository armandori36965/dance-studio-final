import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                // 我們不需要在這裡處理 public/css/schedule.css
                // 因為它是直接透過 <link> 標籤載入的，而非透過 Vite 編譯
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});