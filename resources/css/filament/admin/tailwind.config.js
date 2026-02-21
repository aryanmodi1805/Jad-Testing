import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './resources/views/livewire/*.blade.php',
        './resources/**/*.blade.php',
        './resources/**/**/*.blade.php',
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './resources/views/vendor/themes/filament/**/*.blade.php',
        './vendor/solution-forest/filament-tree/resources/**/*.blade.php',
        './vendor/awcodes/filament-table-repeater/resources/**/*.blade.php',
        './vendor/archilex/filament-filter-sets/**/*.php',
        './resources/views/forms/**/*.blade.php',
        './resources/views/components/**/*.blade.php',
        './app/Providers/AppServiceProvider.php',

    ],
    safelist:[
        'h-14',
        'max-h-[45px]',
        'max-h-[50px]',
        'max-h-[60px]',
        'animate-pulse',
        'animate-bounce',
        'animate-ping',
        'animate-spin',
        'animate-[wiggle_1s_ease-in-out_infinite]','!gap-0'
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#e3e8f1',
                    100: '#94a5c8',
                    200: '#4c649d',
                    300: '#354f8f',
                    400: '#203a80',
                    500: '#0c2371',
                    600: '#091d61',
                    700: '#061752',
                    800: '#041143',
                    900: '#030c35',
                },
                secondary: {
                    50: '#e7edf9',
                    100: '#b8c9ed',
                    200: '#8ba5df',
                    300: '#6080d0',
                    400: '#385bbf',
                    500: '#2547b6',
                    600: '#1f3d9e',
                    700: '#193286',
                    800: '#0d1f59',
                    900: '#040d30',
                },
            },
        },
    },
    plugins: [
        // ...

    ],
}
