<div>
    <div class="relative w-full h-[50vh] bg-cover max-md:bg-center" style="background-image: url('{{app(\App\Settings\HeroesSettings::class)->getSubHero()}}');">
        <div
            class="absolute top-0 flex flex-col items-center justify-center max-sm:justify-center w-full h-full">
            <h1 class="text-6xl text-gray-700 text-center font-bold leading-normal max-sm:!text-5xl">{!! __('string.how_it_works') !!}</h1>

        </div>
    </div>
</div>
