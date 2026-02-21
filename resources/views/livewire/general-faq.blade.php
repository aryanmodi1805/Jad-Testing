<div>
    @teleport('main')
    <div class="min-h-full flex flex-col gap-6 bg-[#f9f9fa]">
        <div class="relative bg-center w-full h-[50rem] max-md:bg-center " style="background-image: url('{{app(\App\Settings\HeroesSettings::class)->getSubHero()}}');">
            <div
                class="flex flex-col items-center justify-center max-sm:justify-center w-full h-full">
                <h1 class="text-6xl text-center text-primary-500 font-bold leading-normal max-lg:!text-5xl ">{!! __('string.faq.full') !!}</h1>
                <h3 class="text-xl text-center px-6  text-gray-700 font-medium leading-normal max-sm:!text-lg ">{!! __('string.faq.sub-title') !!}</h3>
                <div class="mt-12 w-1/2 max-sm:mt-12 max-sm:w-5/6 ">
                    <livewire:simple-search/>
                </div>

            </div>
        </div>
        <livewire:general-faq-body/>
    </div>

    @endteleport


    @teleport('main')
    <!-- Footer -->
    <div class="bg-[#f9f9fa]">
        <livewire:footer/>
    </div>
    <!-- Footer -->

    @endteleport
</div>
