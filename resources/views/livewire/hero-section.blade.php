@php
    $settings = app(\App\Settings\HeroesSettings::class);
    $text = app()->getLocale() == 'ar' ? $settings->text_ar : $settings->text_en;
@endphp

<section class="z-10 w-full">
    <div class="relative w-full h-screen">
        <x-image class="object-cover w-full h-full" src="{{$settings->getMainHero()}}" alt="Hero Image"/>
        <div class="tip-text absolute top-0 flex flex-col items-center justify-center max-sm:justify-center w-full h-full ">

            @if($text != null)
               {!! tiptap_converter()->asHTML($text) !!}
            @else
                <h1 class="text-6xl text-center text-white/80 font-bold leading-normal max-sm:!text-5xl dark:text-white">{!! __('string.hero_title') !!}</h1>
                <p class="mt-6 text-2xl text-center text-white opacity-70 max-sm:text-2xl ltr:font-mtb ltr:font-medium rtl:font-noto  dark:text-white/80">{{ __('string.hero_subtitle') }}</p>
            @endif

            <div class="mt-12 w-1/3 max-sm:w-5/6">
                <livewire:search-services />

            </div>
        </div>
    </div>
</section>
