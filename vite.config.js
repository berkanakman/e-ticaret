import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/scss/admin/app.scss',
                'resources/js/admin/app.js',
                'resources/js/admin/product_features_variants.js',
            ],
            refresh: true,
        }),
    ],
});
