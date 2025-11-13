import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    // The base path should be root for SPAs on Vercel
    base: '/',
    plugins: [
        vue(),
    ],
    build: {
        // This will create a 'dist' folder inside your 'laravel' directory,
        // which is exactly what your Vercel settings are looking for.
        outDir: 'dist',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            // Vite will automatically use your `index.html` as the entry point.
            output: {
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