import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Customer/**/*.php',
        './resources/views/filament/customer/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './resources/views/livewire/*.blade.php',
        './resources/views/components/**/*.blade.php',
        './resources/views/forms/**/*.blade.php',
        './app/Providers/AppServiceProvider.php',


    ],


    safelist: [
        'h-14',
        'max-h-[45px]',
        'max-h-[50px]',
        'max-h-[60px]',
        'animate-pulse',
        'animate-bounce',
        'animate-ping',
        'animate-spin',
        'animate-[wiggle_1s_ease-in-out_infinite]',
        '!ring-danger-600', 'dark:!ring-danger-500',
        '!ring-offset-2', '!ring-2', '!rounded-md', '!gap-0'
    ],
    theme: {
        extend: {
            fontFamily: {
                mt: ['Arial Rounded MT', "sans-serif"],
                mtb: ["Arial Rounded MT Bold", "sans-serif"],
                noto: ["Noto Sans Arabic", "sans-serif"],
                sans: ["Gordita", "sans-serif"],
                arabic: ["Bahij", "sans-serif"],


            },
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
