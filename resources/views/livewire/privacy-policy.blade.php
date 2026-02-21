<div class="relative min-h-screen bg-[#f9f9fa]">

    <div class="absolute bg-cover w-full h-[50vh] max-md:bg-center" style="background-image: url('{{app(\App\Settings\HeroesSettings::class)->getSubHero()}}');">

    </div>
    <div class="flex flex-col p-6 gap-20 items-center">
        <div class="container  p-12 bg-white  overflow-hidden min-h-[40rem] z-10 mt-24">
            <h1 class="text-6xl text-center text-gray-800 font-bold leading-normal max-sm:!text-3xl">@lang('string.privacy-policy')</h1>
            <div class="container flex flex-col gap-4 pb-12 pt-28">
                {!!html_entity_decode( app(\App\Settings\PrivacySettings::class)->getPrivacyPolicy(app()->getLocale()))!!}
            </div>
        </div>

    </div>

    <section>
        <div class="container mx-auto mt-12 flex flex-col items-center">
            <x-image class="h-20" src="/assets/logo/logo.svg" alt="Evantto"/>
        </div>
    </section>
    <livewire:footer/>

</div>
