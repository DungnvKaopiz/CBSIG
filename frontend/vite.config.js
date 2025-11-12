import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';


export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            clientPort: 8000,
        },
        proxy: {
            '/api': {
                target: process.env.VITE_API_URL || 'http://nginx:80',
                changeOrigin: true,
            },
        },
    },
    plugins: [
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, 'src/js'),
        },
    },
    build: {
        outDir: 'dist',
        assetsDir: 'assets',
    },
    root: '.',
    publicDir: 'public',
});

