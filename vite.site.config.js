import { defineConfig } from 'vite';
import laravel, {refreshPaths} from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            buildDirectory: 'site',
            input: [
                'resources/css/site/theme.css',
            ],
            refresh: true,
        }),
    ],
});
