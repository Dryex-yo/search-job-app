import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import compression from 'vite-plugin-compression';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        compression({
            algorithm: 'brotli',
            ext: '.br',
        }),
        compression({
            algorithm: 'gzip',
            ext: '.gz',
        }),
    ],
    build: {
        chunkSizeWarningLimit: 1000,
        assetsInlineLimit: 4096,
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
            },
        },
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    if (id.includes('node_modules')) {
                        if (id.includes('chart')) {
                            return 'chart-vendor';
                        }
                        if (id.includes('apexcharts')) {
                            return 'chart-vendor';
                        }
                        return 'vendor';
                    }
                },
            },
        },
    },
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },

    server: {
    host: '127.0.0.1', // Memaksa menggunakan IPv4
    port: 5173,
  },
});
