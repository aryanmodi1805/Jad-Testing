<!DOCTYPE html>
<html data-theme = "light"  dir="{{ app()->getLocale()=='ar'? 'rtl':'ltr' }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('icon.png') }}" sizes="32x32" />
    <link rel="icon" href="{{ asset('icon.png') }}" sizes="192x192" />
    <link rel="apple-touch-icon" href="{{ asset('icon.png') }}" />
    <meta name="msapplication-TileImage" content="{{ asset('icon.png') }}" />
    @if (Request::route()->getPrefix() ==getSkyPrefix())

        <x-seo::meta/>
    @else

        {{-- SEO FIXES --}}
        <title>JAD Services | Professional Service Providers in Saudi Arabia</title>
        <meta name="description" content="JAD Services connects you with trusted service providers in Saudi Arabia. Compare prices, book professionals, and get the best service experience.">

        {{-- Canonical URL --}}
        <link rel="canonical" href="https://sa.jad.services/">

        {{-- Hreflang Tags --}}
        <link rel="alternate" hreflang="en" href="https://sa.jad.services/" />
        <link rel="alternate" hreflang="ar" href="https://sa.jad.services/locale/ar" />
        <link rel="alternate" hreflang="x-default" href="https://sa.jad.services/" />

        {{-- Open Graph --}}
        <meta property="og:title" content="JAD Services | Service Providers in Saudi Arabia">
        <meta property="og:description" content="Find and compare trusted service providers in Saudi Arabia with JAD Services.">
        <meta property="og:url" content="https://sa.jad.services/">
        <meta property="og:type" content="website">

        {{-- Conditional Noindex for Login/Cart Pages --}}
        @if(request()->is('customer/login') || request()->is('seller/login') || request()->is('checkout') || request()->is('cart'))
            <meta name="robots" content="noindex, nofollow">
        @endif

    @endif


    @filamentStyles
    <!-- Tabler icons set -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Styles -->
    <!-- Fonts -->
    <link href="https://fonts.cdnfonts.com/css/arial-mt" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@100..900&display=swap" rel="stylesheet">


    <!-- swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link href="{{ asset('assets/font.css') }}" rel="stylesheet" />
    {{--    -----}}
    {{ Vite::useBuildDirectory('site')->withEntryPoints([
        "resources/css/site/theme.css",
    ]) }}
{{--    @vite('resources/css/site/index.css')--}}


</head>


<body  x-data="{ loading: false }" @open-wizard.window="loading = true" @location-set.window="loading = false" @location-error.window="loading = false">
<!-- Navbar -->
<livewire:main-nav  />
<!-- Navbar End-->
<!-- Loader -->
<div x-cloak x-show="loading" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
    <div class="flex flex-col items-center justify-center p-8 bg-white rounded-lg shadow-lg">
        <div class="w-16 h-16 mb-4 border-4 border-blue-500 rounded-full border-t-transparent animate-spin"></div>
        <p class="text-lg font-semibold text-gray-700">{{__('labels.please_wait')}}</p>
    </div>
</div>
<!-- Loader End -->

<!-- wizard -->
<livewire:front-wizard  wire:key="{{ uniqid() }}"/>
<!-- wizard End -->
@hasSection('breadcrumbs')
    @yield('breadcrumbs')
@else
    @if ( isset($breadcrumbs))
        <div class="bg-white">
            <div class="container px-3 py-2 mx-auto">



                @if (isset($breadcrumbs))
                    <div class="flex items-center py-4 m-10 overflow-x-auto whitespace-nowrap">

                        <a href="{{ route('filament.guest.pages.home') }}"
                           class="text-gray-600 dark:text-gray-200 hover:text-blue-900 hover:underline focus:text-blue-900 focus:underline">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>


                        </a>
                        <span class="mx-2 text-gray-500 dark:text-gray-300 rtl:-scale-x-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                              d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                              clip-rule="evenodd" />
                                    </svg>
                                </span>
                        {{ $breadcrumbs }}

                    </div>




                @endif

            </div>
        </div>
    @else
        {{--    {{ Breadcrumbs::render(Route::currentRouteName()) }}  --}}
    @endif

@endif

<div>
    {{ $slot }}
</div>
{{--<x-footer></x-footer>--}}
@livewire('notifications')

@filamentScripts
@yield('js')


</body>

</html>
