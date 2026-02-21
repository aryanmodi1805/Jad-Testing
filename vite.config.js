import { defineConfig } from 'vite'
import laravel, { refreshPaths } from 'laravel-vite-plugin'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/local.css',
                'resources/js/app.js',
                'resources/css/filament/seller/theme.css',
                'resources/css/filament/admin/theme.css',
                'resources/css/filament/customer/theme.css',
                'resources/js/swiper.js',
                'resources/js/worker.js',
                'resources/js/notification.js',

            ],
            refresh: [
                ...refreshPaths,
                'app/Livewire/**',
            ],
        }),
    ],
})
