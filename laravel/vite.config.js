import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    build: {
        // Output directory for Vercel. It's outside the Laravel folder.
        outDir: '../frontend-deploy',
        emptyOutDir: true, // Cleans the directory before building
        manifest: true, // Generates manifest.json for inspection
        rollupOptions: {
            // By removing the 'input' here, Vite will automatically
            // use the `index.html` in this directory as the entry point.
            output: {
                // Asset naming convention
                entryFileNames: `assets/[name]-[hash].js`,
                chunkFileNames: `assets/[name]-[hash].js`,
                assetFileNames: `assets/[name]-[hash].[ext]`,
            },
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '~': path.resolve(__dirname, 'resources/js'),
            '@ai': path.resolve(__dirname, 'ai'),
            'ziggy-js': path.resolve(__dirname, 'vendor/tightenco/ziggy/dist/index.esm.js'),
        }
    }
});