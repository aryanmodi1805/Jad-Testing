import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './resources/views/livewire/*.blade.php',
        './resources/**/*.blade.php',
        './resources/**/**/*.blade.php',
        './resources/views/forms/**/*.blade.php',
        './vendor/jaocero/radio-deck/resources/views/**/*.blade.php',
        './vendor/lara-zeus/core/resources/views/**/*.blade.php',
        './vendor/lara-zeus/sky/resources/views/themes/**/*.blade.php',

    ],
    plugins: [
        // ...

    ],
}
