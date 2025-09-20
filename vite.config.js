import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        hmr: {
            host: 'localhost',
        },
    },
    build: {
        rollupOptions: {
            external: (id) => {
                // Don't externalize assets that should be processed by Vite
                if (id.startsWith('/assets/')) {
                    return false;
                }
                return false;
            }
        }
    },
    publicDir: 'public',
});
