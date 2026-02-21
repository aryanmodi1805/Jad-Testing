import preset from '../../vendor/filament/filament/tailwind.config.preset'

module.exports = {
    presets: [preset],
    content: [
        './resources/views/livewire/*.blade.php',
        './resources/views/**/*.blade.php',
        './resources/views/forms/**/*.blade.php',
        './resources/views/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './resources/views/vendor/themes/filament/**/*.blade.php',
        './vendor/solution-forest/filament-tree/resources/**/*.blade.php',
        './vendor/awcodes/filament-table-repeater/resources/**/*.blade.php',
        './vendor/jaocero/radio-deck/resources/views/**/*.blade.php',
    ],
    darkMode: 'class', // or 'media' or false
    theme: {
        extend: {
            fontFamily: {
                mt: ['Arial Rounded MT', "sans-serif"],
                mtb: ["Arial Rounded MT Bold", "sans-serif"],
                noto: ["Noto Sans Arabic", "sans-serif"],
                sans: ["Gordita", "sans-serif"],
                arabic: ["Bahij", "sans-serif"],


            },

            keyframes: {
                spin: {
                    to: {transform: 'rotate(360deg)'},
                },
            },
            animation: {
                spin: 'spin 1s linear infinite',
            },
            boxShadow: {
                '3xl': '0 35px 60px -15px rgba(0, 0, 0, 0.3)',
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
    plugins: [],
}
