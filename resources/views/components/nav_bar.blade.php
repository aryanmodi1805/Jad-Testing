<nav class="sticky w-full top-0 z-50 bg-white border-b boreder-solid boreder-[#817e95] h-[5.5rem] max-sm:h-[3rem]">
    <div class="container relative z-10 flex items-center justify-between h-full px-8 mx-auto ">
        <div class="flex items-center gap-4">
            <div class="h-[2rem] max-sm:h-[1.5rem]">
                <x-image class="h-full" width="auto" src="{{ asset('assets/logo/logo.svg') }}" alt="evento"/>
            </div>
            <div x-data="{ visible: false }" class="relative inline-block text-left">
                <div>
                    <button @click="visible = !visible" type="button"
                        class="flex w-full justify-center items-center gap-x-1.5 rounded-md text-xl font-mtb font-light bg-white px-3 py-2  text-gray-900  hover:bg-gray-50 max-sm:text-base"
                        id="menu-button" aria-expanded="true" aria-haspopup="true">
                        <span  class="font-light font-mtb">
                            Services

                        </span>
                        <i class="ti ti-chevron-down"></i>
                    </button>
                </div>

                <div x-show="visible"
                    class="absolute left-1/2 transform -translate-x-1/2 p-4 z-10 mt-2 w-[20rem] origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                    role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                    <div x-data="{ actions : [
                            {title: 'Consultant', icon: 'ti ti-user',t:'consultant'},
                            {title: 'Programmer', icon: 'ti ti-code',t:'programmer'},
                            {title: 'Digital marketing', icon: 'ti ti-speakerphone',t:'digital_marketing'},
                            {title: 'Cooker room', icon: 'ti ti-chef-hat',t:'cooker_room'},
                            {title: 'Driver', icon: 'ti ti-car',t:'driver'},
                            {title: 'Accountanting', icon: 'ti ti-calculator',t:'accountanting'},
                            {title: 'Web Design', icon: 'ti ti-device-laptop',t:'web_design'}
                            ]}" class="py-1" role="none">
                        <!-- Active: "bg-gray-100 text-gray-900", Not Active: "text-gray-700" -->

                        <template x-for="(action,index) in actions">

                            <a href="#"
                                class="flex items-center gap-4 px-4 py-2 text-sm text-gray-700 rounded-md cursor-pointer pointer-events-auto hover:bg-gray-50 "
                                role="menuitem" tabindex="-1" id="menu-item-0">
                                <span class="p-2 bg-[#92298f2c] rounded-md ">
                                    <span
                                        class="bg-gradient-to-r from-[#CE4F57]  to-[#92298D] inline-block text-transparent bg-clip-text">

                                        <i :class="`${action.icon} text-[1.1rem] `"></i>
                                    </span>
                                </span>
                                <span class="text-[#747474] text-[1.1rem]"></span>
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div x-data class="flex items-center gap-4">
            <button type="button"
                class="pointer-events-auto cursor-pointer font-mtb font-semibold hover:text-[#92298D] max-sm:text-xs"
                >Login</button>

            <button type="button"
                class="pointer-events-auto cursor-pointer rounded-md bg-gradient-to-r from-[#CE4F57]  to-[#92298D] inline-block px-8 py-2 font-mtb   text-white hover:bg-gradient-to-r hover:from-[#ad4048]  hover:to-[#751e71] hover:inline-block max-sm:text-xs max-sm:py-[0.3rem] max-sm:px-[0.5rem] max-sm:rounded-full"
                >Join
                us</button>
                @livewire('filament-language-switch',["key"=>'fls-outside-panels'])

        </div>

    </div>
</nav>
