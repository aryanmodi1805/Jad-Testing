<!DOCTYPE html>
<html data-theme="light" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"
      lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('icon.png') }}" sizes="32x32"/>
    <link rel="icon" href="{{ asset('icon.png') }}" sizes="192x192"/>
    <link rel="apple-touch-icon" href="{{ asset('icon.png') }}"/>
    <meta name="msapplication-TileImage" content="{{ asset('icon.png') }}"/>


        <title>
            @lang('string.page-not-found', [], app()->getLocale())
        </title>




    @filamentStyles
    <!-- Tabler icons set -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <!-- Styles -->
    <!-- Fonts -->
    <link href="https://fonts.cdnfonts.com/css/arial-mt" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@100..900&display=swap" rel="stylesheet">


    <!-- swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link href="{{ asset('assets/font.css') }}" rel="stylesheet"/>
    {{--    -----}}
    {{ Vite::useBuildDirectory('site')->withEntryPoints([
        "resources/css/site/theme.css",
    ]) }}

</head>


<body>
<livewire:not-found-page/>
</body>

</html>




