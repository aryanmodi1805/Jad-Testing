@php
$settings = app(\App\Settings\GeneralSettings::class)
@endphp

<section>
    <div class="relative bg-center h-full w-full bg-[#f9f9fa] max-md:h-[40rem]">
        <x-image class="w-full object-cover max-md:h-full" src="assets/summery-bg.png"></x-image>
        <div
            class="absolute top-36 start-0 end-0 container mx-auto w-full h-full flex flex-col items-center justify-center gap-8 max-lg:h-fit max-md:h-full">
            <div class="h-full w-full grid grid-cols-3 place-items-center gap-4 items-center text-white max-md:grid-cols-2 max-sm:grid-cols-1 max-sm:gap-8">
                <div class="w-full justify-start flex gap-4 items-center max-sm:w-fit ">
                    <x-image class="w-20 max-md:w-14" src="assets/summry/costumer.png"></x-image>
                    <div class="w-full flex flex-col gap-2">
                        <span class="text-6xl max-md:text-5xl">{{$customersCount + $settings->customers_count}}
                                                <span class="text-3xl">
                            +
                        </span>
                        </span>
                        <span class="text-xl uppercase">@lang('string.satisfied-clients')</span>

                    </div>
                </div>
                <div class="flex gap-4 items-center">
                    <x-image class="w-20 max-md:w-14" src="assets/summry/happy.png"></x-image>
                    <div class="flex flex-col gap-2">
                        <span class="text-6xl max-md:text-5xl">{{$projectsCompleted + $settings->projects_completed}}
                                                <span class="text-3xl">
                            +
                        </span>
                        </span>
                        <span class="text-xl uppercase">@lang('string.projects-completed')</span>

                    </div>
                </div>
                <div class="w-full  flex gap-4 justify-end items-center max-md:justify-start max-sm:justify-center">
                    <x-image class="w-20 max-md:w-14" src="assets/summry/support.png"></x-image>
                    <div class="flex flex-col gap-2">
                        <span class="text-6xl max-md:text-5xl">{{$teamsCount + $settings->teams_count}}
                        <span class="text-3xl">
                            +
                        </span>
                        </span>
                        <span class="text-xl uppercase">@lang('string.support-teams')</span>

                    </div>
                </div>
            </div>
            <div class="w-full h-full flex gap-12 max-lg:flex-col max-md:gap-0">
                <div class="w-full">

                </div>
                <div class="w-full">
                    <h4 class="text-white text-4xl rtl:text-5xl font-bold text-end max-lg:text-center leading-[4rem] max-md:text-3xl max-md:rtl:text-3xl">
                        @lang('string.summery-section-title')
                    </h4>
                </div>

            </div>
        </div>
    </div>
</section>
