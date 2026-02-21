@props([
    'color' => 'primary',
    'attachment' => false
])

<span
      {{
            $attributes->class([
                'fi-badge me-2 flex items-center justify-center gap-x-1 rounded-md text-md font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 fi-color-primary',
                'shadow-md hover:bg-custom-100 dark:hover:bg-custom-50/10' => $attachment,
])->style([
                \Filament\Support\get_color_css_variables(
                    $color,
                    shades: [
                        50,
                        100,
                        400,
                        600,
                    ],
                    alias: 'badge',
                ) => $color !== 'gray',
            ])
}}
      >
    <span class="grid">
        <span class="truncate">
            {{$slot}}
        </span>
    </span>

</span>
