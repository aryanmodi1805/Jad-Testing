<div class="h-fit">
    <div class="relative w-full h-[50vh] bg-cover max-md:bg-center"
         style="background-image: url('{{app(\App\Settings\HeroesSettings::class)->getSubHero()}}');">

        <div
            class="absolute top-0 flex flex-col gap-4 items-center justify-center max-sm:justify-center w-full h-full ">
            <h3 class="text-4xl text-center text-gray-700 font-medium leading-normal max-sm:!text-2xl ">{!! __('string.how_it_works') !!}</h3>
            <h1 class="text-6xl text-center text-gray-700 font-bold leading-normal max-sm:!text-5xl ">{!! __('string.for-pros') !!}</h1>
            <a href="/seller"
               class="text-white bg-gradient-to-r  from-primary-500  to-secondary-500  font-medium rounded-lg text-lg px-4 py-2">@lang('string.join-as-seller')</a>

        </div>
    </div>
</div>
