<nav x-data="{sidebarOpen:false , visible: false}"
     class="sticky top-0 z-50 w-full bg-white border-b border-[#817e95] h-[5.5rem] max-sm:h-[3rem]">
    <div x-show="sidebarOpen" class="absolute z-10 w-[24rem] h-screen bg-white top-0 start-0 shadow-lg sm:hidden">
        <div class="w-full h-full flex flex-col gap-12 p-8">

            <div class="flex justify-between items-center">
                <a class="h-[2rem]" href="/">
                    <x-image class="h-full" width="auto" src="{{ asset('assets/logo/logo.svg') }}" alt="evento"/>
                </a>
                <button @click="sidebarOpen = false" type="button"
                        class="hover:bg-[#92298d0a] p-4 rounded-full"
                        id="sidebar-button" aria-expanded="true" aria-haspopup="true">
                    <i class="ti ti-x"></i>
                </button>
            </div>


            <div class="h-full flex flex-col justify-between gap-4">
                <a @click="visible=!visible"
                   class="w-full flex justify-between items-center text-lg ltr:font-mtb rtl:font-noto font-light text-gray-900 hover:text-[#92298D] hover:bg-[#92298d0a] p-4 rounded-md">
                    @lang('services.services.plural')
                    <i class="ti ti-chevron-down"></i>
                </a>

                <div class="flex flex-col items-center gap-4 w-full text-center">
                    @auth('seller')
                        <a href="{{ route('filament.seller.pages.dashboard-extend',["tenant" => getSubdomain()]) }}"
                           class="w-full pointer-events-auto cursor-pointer rounded-md bg-gradient-to-r from-[#CE4F57] to-[#92298D] inline-block px-8 py-2 font-mtb text-white hover:bg-gradient-to-r hover:from-[#ad4048] hover:to-[#751e71] max-sm:text-lg ">
                            @lang('auth.switch_to_seller_dashboard')
                        </a>
                    @else

                        <a href="{{ route('filament.seller.auth.login') }}"
                           class="w-full pointer-events-auto cursor-pointer rounded-md bg-gradient-to-r from-[#CE4F57] to-[#92298D] inline-block px-8 py-2 font-mtb text-white hover:bg-gradient-to-r hover:from-[#ad4048] hover:to-[#751e71] max-sm:text-lg ">
                            @lang('auth.login_as_pro')
                        </a>
                    @endauth
                    @auth('customer')
                        <a href="{{ route('filament.customer.pages.dashboard-extend',["tenant" => getSubdomain()]) }}"
                           class="w-full pointer-events-auto cursor-pointer font-mtb font-semibold hover:text-[#92298D] hover:bg-[#92298d0a] p-2 rounded-md max-sm:text-lg">
                            @lang('auth.customer_dashboard')
                        </a>
                    @else
                        <a href="{{ route('filament.customer.auth.login') }}"
                           class="w-full pointer-events-auto cursor-pointer font-mtb font-semibold hover:text-[#92298D] hover:bg-[#92298d0a] p-2 rounded-md max-sm:text-lg">
                            @lang('auth.login')
                        </a>
                    @endauth

                </div>
            </div>

        </div>
    </div>
    <div class="container relative flex items-center justify-between h-full mx-auto ">
        <div class="flex items-center gap-4">
            <div class="h-[2rem] max-sm:h-[1.5rem]">
                <a href="/">
                    <x-image class="h-full" width="auto" src="{{ asset('assets/logo/logo.svg') }}" alt="evento"/>
                </a>
            </div>

            <div class="relative inline-block text-left">
                <div class="max-sm:hidden">
                    <button @click="visible = !visible" type="button"
                            class="flex items-center gap-x-1.5 rounded-md text-xl font-mtb font-light bg-white px-4 py-2 text-gray-900 hover:bg-gray-50 max-sm:text-base"
                            id="menu-button" aria-expanded="true" aria-haspopup="true">
                        <span class="font-light font-mtb">@lang('services.services.plural')</span>
                        <i class="ti ti-chevron-down"></i>
                    </button>
                </div>
                <div x-cloak x-show="visible" @click.away="visible = false"
                     class="absolute rtl:right-0 ltr:left-0 p-4 z-10 mt-2 w-[22rem] origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 border border-blue-500 focus:outline-none"
                     role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                    <div class="py-1" role="none">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900 ltr:text-left rtl:text-right">@lang('services.categories.plural')</h3>
                            <div class="grid grid-cols-2 gap-4">
                                @foreach($categories as $category)
                                    <a href="#"
                                       class="flex items-center gap-4 px-4 py-2 text-sm text-gray-700 rounded-md cursor-pointer hover:bg-gray-50 rtl:text-right ltr:text-left"
                                       role="menuitem" tabindex="-1" id="menu-item-0">
                                        <span class="p-2 bg-[#92298f2c] rounded-md">
                                            <span
                                                class="bg-gradient-to-r from-[#CE4F57] to-[#92298D] inline-block text-transparent bg-clip-text">
                                                <i class="{{ $category->icon_front }} text-[1.1rem]"></i>
                                            </span>
                                        </span>
                                        <span class="text-[#747474] text-[1.1rem]">{{ $category->name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 ltr:text-left rtl:text-right">@lang('services.services.popular_services')</h3>
                            <div class="grid grid-cols-1 gap-4">
                                @foreach($popularServices as $service)
                                    <a href="#"
                                       wire:click="$dispatch('open-wizard', { serviceId: '{{ $service->id }}' })"
                                       class="block px-4 py-2 text-sm text-gray-700 rounded-md cursor-pointer hover:bg-gray-50 rtl:text-right ltr:text-left"
                                       role="menuitem" tabindex="-1" id="menu-item-1">
                                        {{ $service->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            @auth('seller')
                <a href="{{ route('filament.seller.pages.dashboard-extend',["tenant" => getSubdomain()]) }}"
                   class="pointer-events-auto cursor-pointer rounded-md bg-gradient-to-r from-[#CE4F57] to-[#92298D] inline-block px-8 py-2 font-mtb text-white hover:bg-gradient-to-r hover:from-[#ad4048] hover:to-[#751e71] max-sm:hidden">
                    @lang('auth.switch_to_seller_dashboard')
                </a>
            @else

                <a href="{{ route('filament.seller.auth.login') }}"
                   class="pointer-events-auto cursor-pointer rounded-md bg-gradient-to-r from-[#CE4F57] to-[#92298D] inline-block px-8 py-2 font-mtb text-white hover:bg-gradient-to-r hover:from-[#ad4048] hover:to-[#751e71] max-sm:hidden">
                    @lang('auth.login_as_pro')
                </a>
            @endauth
            @auth('customer')
                <a href="{{ route('filament.customer.pages.dashboard-extend',["tenant" => getSubdomain()]) }}"
                   class="pointer-events-auto cursor-pointer font-mtb font-semibold hover:text-[#92298D] max-sm:hidden">
                    @lang('auth.customer_dashboard')
                </a>
            @else
                <a href="{{ route('filament.customer.auth.login') }}"
                   class="pointer-events-auto cursor-pointer font-mtb font-semibold hover:text-[#92298D] max-sm:hidden">
                    @lang('auth.login')
                </a>
            @endauth

            <a @click="sidebarOpen = !sidebarOpen" class="sm:hidden">
                <i class="ti ti-menu-2"></i>
            </a>
            @livewire('filament-language-switch', ["key" => 'fls-outside-panels'])
        </div>
    </div>
</nav>
