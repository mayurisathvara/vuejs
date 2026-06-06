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
        host: '0.0.0.0',
        hmr: {
            host: '192.168.1.7',
        },
        cors: true,
    },
    build: {
        rollupOptions: {
            external: (id) => {
                // Don't externalize assets that should be processed by Vite
                if (id.startsWith('/assets/')) {
                    return false;
                }
                return false;
            },
            output: {
                manualChunks: {
                    'vendor-vue': ['vue', 'vue-router', 'pinia', '@vueuse/core'],
                    'vendor-axios': ['axios'],
                    'vendor-charts': ['chart.js'],
                    'vendor-flatpickr': ['flatpickr'],
                },
            },
        }
    },
    // Laravel already serves `/public` directly; avoid copying public/ into public/build
    // (Vite 7 warns when outDir is inside publicDir)
    publicDir: false,
});
