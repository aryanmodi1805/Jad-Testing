<footer class="bg-cover w-full" style="background-image: url('{{ asset('assets/footer.png') }}');">

    <div x-data class="container relative px-8 mx-auto pb-16 pt-56">
        <div class="flex items-start justify-between gap-6 max-md:flex-col max-md:items-center">
            <div class="flex flex-col gap-4 items-center">
                <div class="h-[9rem]">
                    <div class="h-[8rem]">
                        <a href="/">
                            <x-image class="h-full" width="auto" src="{{ asset('assets/logo/logo-white.png') }}"
                                     alt="eventto"/>
                        </a>
                    </div>
                </div>
                <div class="flex justify-center items-center w-full gap-4">

                    @foreach($socialMedia as $key => $social)
                        <a href="{{$social}}" target=”_blank”
                           class="w-10 h-10 hover:bg-primary-50 hover:bg-opacity-25 bg-transparent rounded-full flex justify-center items-center border border-white">
                            <i class="text-2xl text-white ti ti-brand-{{$key}} "></i>
                        </a>
                    @endforeach

                </div>
                <div class="flex gap-2 items-stretch justify-center max-md:flex-col scale-90">
                    <button type="button"
                            @click="window.open('https://apps.apple.com/sa/app/jad-services/id6751058593', '_blank')"
                            class="text-white bg-[#050708] flex gap-2 hover:bg-[#050708]/80 focus:ring-4 focus:outline-none focus:ring-[#050708]/50 font-medium rounded-lg text-sm px-5 py-1 text-center inline-flex items-center dark:hover:bg-[#050708]/40 dark:focus:ring-gray-600 me-2 mb-2">
                        <i class="ti ti-brand-apple-filled text-3xl"></i>
                        <div class="flex flex-col items-start">
                            <span class="text-[0.7rem] ">@lang('labels.get-it-on')</span>
                            <span class="font-bold text-lg">App Store</span>
                        </div>
                    </button>

                    <button type="button"
                            @click="window.open('https://play.google.com/store/apps/details?id=services.jad.app', '_blank')"
                            class="text-white bg-[#050708] flex gap-2 hover:bg-[#050708]/80 focus:ring-4 focus:outline-none focus:ring-[#050708]/50 font-medium rounded-lg text-sm px-5 py-1 text-center inline-flex items-center dark:hover:bg-[#050708]/40 dark:focus:ring-gray-600 me-2 mb-2">
                        <i class="ti ti-brand-google-play text-3xl"></i>
                        <div class="flex flex-col items-start">
                            <span class="text-[0.7rem] ">@lang('labels.get-it-on')</span>
                            <span class="font-bold text-lg">Google Play</span>
                        </div>
                    </button>

                </div>
                {{--                <livewire:country-selector/>--}}

            </div>

            <div class="flex gap-16 max-lg:flex-col">
                <div class="flex flex-col items-start gap-4 max-md:items-center">
                    <h5 class="text-2xl text-white">
                        @lang('footer.jad')
                    </h5>
                    <div class="flex flex-col gap-4 items-start text-gray-100 max-md:items-center">
                        <a href="{{route('filament.guest.pages.home') }}">
                            @lang('footer.home')
                        </a>
                        <a href="/about">@lang('footer.about')</a>
                        <a href="{{route('filament.guest.pages.faq')}}">
                            @lang('footer.faq')
                        </a>

                        <a href="{{route('filament.guest.pages.blog')}}">
                            @lang('footer.blog')
                        </a>

                        <a href="/privacy-policy">
                            @lang('string.privacy-policy')
                        </a>
                    </div>
                </div>
                <div class="flex flex-col items-start gap-4 max-md:items-center">
                    <h5 class="text-2xl text-white">
                        @lang('footer.for-customers')
                    </h5>
                    <div class="flex flex-col gap-4 items-start text-gray-100 max-md:items-center">
                        <a href="{{route('filament.guest.pages.home')}}">
                            @lang('footer.search-for-service')
                        </a>

                        <a href="/customer/login">
                            @lang('footer.login')
                        </a>


{{--                        <a href="/get-the-app">--}}
{{--                            @lang('footer.mobile-app-android')--}}
{{--                        </a>--}}
{{--                        <a href="/get-the-app">--}}
{{--                            @lang('footer.mobile-app-ios')--}}
{{--                        </a>--}}
                        <a href="/how-it-works/customers/">
                            @lang('footer.how-it-works')
                        </a>

                        <a href="/customer-agreement">
                            @lang('footer.customer-agreement')
                        </a>
                    </div>
                </div>

                <div class="flex flex-col items-start gap-4 max-md:items-center">
                    <h5 class="text-2xl text-white">
                        @lang('footer.for-sellers')
                    </h5>
                    <div class="flex flex-col gap-4 items-start text-gray-100 max-md:items-center">
                        <a href="/seller/login">
                            @lang('footer.login')
                        </a>

                        <a href="/seller/register">
                            @lang('footer.join-as-seller')
                        </a>

                        <a href="/how-it-works/sellers">
                            @lang('footer.how-it-works')
                        </a>
{{--                        <a href="/seller/pricing">--}}
{{--                            @lang('footer.pricing')--}}
{{--                        </a>--}}

                        <a href="/seller-agreement">
                            @lang('footer.seller-agreement')
                        </a>


                    </div>
                </div>
                <div class="flex flex-col items-start gap-4 max-md:items-center">
                    <h5 class="text-2xl text-white">
                        @lang('footer.contact-details')
                    </h5>
                    <div class="flex flex-col gap-4 text-white items-start text-gray-100 max-md:items-center">

                        @unless($location == null)
                            <a class="w-full flex items-center gap-4 max-lg:flex-col">
                                <i class="text-2xl ti ti-map-pin"></i>
                                <p class="text-xl text-white ">{{$location}}</p>
                            </a>
                        @endunless

                        <a class="w-full flex items-center gap-4 max-lg:flex-col" href="mailto:{{$email}}">
                            <i class="text-2xl ti ti-mail"></i>
                            <p class="text-xl text-white ">{{$email}}</p>
                        </a>

                        <a class="w-full flex items-center gap-4 max-lg:flex-col" href="tel:{{$phone}}">
                            <i class="text-2xl ti ti-phone"></i>
                            <p class="text-xl text-white " dir="ltr">{{$phone}}</p>
                        </a>


                    </div>
                </div>
            </div>

        </div>
        <div class="py-4">
            <hr class="my-12 border-gray-200 sm:mx-auto dark:border-gray-700 lg:my-8"/>
        </div>
        <div class="w-full flex items-center justify-center max-sm:flex-col max-sm:justify-center gap-4 text-gray-100 text-lg ">
            <p class="text-white rtl:font-arabic">© 2024 {{ request()->getHost() }}. @lang('footer.rights-reserved')</p>
        </div>
    </div>
</footer>
