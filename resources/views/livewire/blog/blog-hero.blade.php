<section class="w-full">
    <div class="relative bg-center w-full h-[50vh] max-md:bg-center " style="background-image: url('{{app(\App\Settings\HeroesSettings::class)->getSubHero()}}');">
        <div
            class="flex flex-col items-center justify-center max-sm:justify-center w-full h-full">
            <h1 class="text-6xl text-center text-primary-500 font-bold leading-normal max-lg:!text-5xl ">{!! __('blogs.discover-more') !!}</h1>
            <div class="mt-12 w-1/2 max-sm:mt-12 max-sm:w-5/6 ">
                <livewire:blog.search/>
            </div>
        </div>
    </div>
</section>
