<div class="relative  min-h-screen bg-[#f9f9fa]">

    <div class="absolute bg-cover w-full h-[50vh] max-md:bg-center"
         style="background-image: url('{{app(\App\Settings\HeroesSettings::class)->getSubHero()}}');">

    </div>
    <div class="flex p-6 flex-col gap-20 items-center">
        <div class="container p-6 bg-white overflow-hidden min-h-[40rem] z-10 mt-24">

            <section>
                <div
                    class="flex gap-8 items-center py-24 max-md:flex-col max-md:justify-center max-md:text-center max-md:p-6">
                    <div class="w-full flex flex-col items-center gap-4 p-8">
                        <i class="ti ti-braille text-6xl"></i>
                        <h3 class="text-4xl text-center font-bold max-md:text-2xl">{{$about_title ?? __('string.about-header')}}</h3>
                        <p class="text-center text-lg">{{$about_sub_title ?? __('string.about-content')}}</p>
                    </div>
                </div>
            </section>

            <livewire:section
                title="{{ __('string.about-us') }}"
                content="{{$content}}"
                image="{{$image}}"
                imagePosition="end"
                hidden="{{false}}"
            />

            <section id="contact-us" class="w-full mb-12 !bg-transparent rounded-lg overflow-hidden">
                <div class="container mx-auto flex flex-col items-center">

                    <div class="w-full flex flex-col gap-4 items-center mt-12">
                        <h3 class="w-full text-start">@lang('string.contact-us')</h3>
                        <div class="w-full grid gap-4 grid-cols-3 max-md:grid-cols-1">

                            <a href="tel:{{$phone}}"
                               class="w-full flex flex-col col-span-1 items-center justify-center gap-4 p-6 border border-solid border-gray-300 hover:bg-[#92298d0a]">
                                <i class="text-2xl ti ti-phone"></i>
                                <p dir="ltr">{{$phone}}</p>
                            </a>

                            <a href="mailto:{{$email}}"
                               class="w-full flex flex-col col-span-1 items-center justify-center gap-4 p-6 border border-solid border-gray-300 hover:bg-[#92298d0a]">
                                <i class="text-2xl ti ti-mail"></i>
                                <p class="">{{$email}}</p>
                            </a>
                            @unless($location==null)
                                <a href="https://maps.google.com/maps?q={{$lat}},{{$lng}}&hl={{app()->getLocale()}}&z=16"
                                   class="w-full flex flex-col col-span-1 items-center justify-center gap-4 p-6  border border-solid border-gray-300 hover:bg-[#92298d0a]">
                                    <i class="text-2xl ti ti-map-pin"></i>
                                    <p class="text-center">{{$location}}</p>
                                </a>
                            @endunless
                            {{--                            <div--}}
                            {{--                                class="relative overflow-hidden w-full h-[40rem] grid col-span-3 max-md:col-span-1 items-center justify-center gap-4 p-6 border border-solid border-gray-300">--}}

                            {{--                                <div class="absolute w-full h-full">--}}
                            {{--                                    <iframe--}}
                            {{--                                        width="100%"--}}
                            {{--                                        height="100%"--}}
                            {{--                                        style="border:0"--}}
                            {{--                                        loading="lazy"--}}
                            {{--                                        allowfullscreen--}}
                            {{--                                        referrerpolicy="no-referrer-when-downgrade"--}}
                            {{--                                        src="https://maps.google.com/maps?q={{$lat}},{{$lng}}&hl={{app()->getLocale()}}&z=16&amp;output=embed"--}}
                            {{--                                    >--}}
                            {{--                                    </iframe>--}}
                            {{--                                </div>--}}
                            {{--                            </div>--}}


                        </div>
                    </div>

                </div>
            </section>

        </div>


        <x-image class="h-20" src="/assets/logo/logo.svg" alt="Evantto"/>


    </div>
    <livewire:footer/>

</div>
