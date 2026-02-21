{{--<div class="relative shadow-lg rounded-xl bg-white dark:bg-gray-800 p-4 md:p-5 max-w-3xl mx-auto mb-20"--}}
{{--     x-data="{ open: false, selectedService: '{{ $serviceName }}', showAlert: false }" x-on:click.away="open = false"--}}
{{--     :class="{'rtl': document.documentElement.dir === 'rtl'}">--}}
{{--    <div >--}}
{{--            fffff--}}
{{--        {{$this->form}}--}}

{{--        <div class="flex-grow relative">--}}
{{--            <x-filament::input.wrapper class="relative w-full">--}}
{{--                <x-filament::input--}}
{{--                    type="text"--}}
{{--                    wire:model.live.debounce.500ms="searchTerm"--}}
{{--                    name="search"--}}
{{--                    x-model="selectedService"--}}
{{--                    id="search"--}}
{{--                    class="block w-full py-3 pr-10  md:pr-16  rtl:lg:pr-20 ltr:lg:pl-20  shadow-sm rtl:pl-5 ltr:pr-5 rtl:md:pl-7 ltr:md:pr-7 rtl:lg:pl-10 ltr:lg:pr-10 sm:text-sm sm:leading-6 "--}}
{{--                    placeholder="{{ __('labels.search_for_services') }}"--}}
{{--                    @focus="open = true"--}}
{{--                />--}}
{{--                <span wire:loading wire:target="searchTerm,category"--}}
{{--                      class="absolute rtl:right-3 rtl:md:right-5 ltr:left-3 ltr:md:left-5 top-1/2 transform -translate-y-1/2">--}}
{{--                    <i class="fas fa-spinner fa-spin text-lg md:text-xl text-primary-600 dark:text-primary-600"></i>--}}
{{--                </span>--}}
{{--                @if($services->isNotEmpty())--}}
{{--                    <ul class="absolute z-20 w-full bg-white shadow-lg rounded-xl dark:bg-gray-800 max-h-60 overflow-auto mt-1"--}}
{{--                        x-show="open">--}}
{{--                        @foreach($services as $service)--}}
{{--                            <li--}}
{{--                                class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"--}}
{{--                                @click="selectedService = '{{ $service->name }}'; open = false; $wire.selectService('{{ $service->id }}'); showAlert = false"--}}
{{--                            >--}}
{{--                                {{ $service->name }}--}}
{{--                            </li>--}}
{{--                        @endforeach--}}
{{--                    </ul>--}}
{{--                @endif--}}
{{--            </x-filament::input.wrapper>--}}
{{--        </div>--}}

{{--        <div class="flex items-center space-x-2 rtl:space-x-reverse">--}}
{{--            <button--}}
{{--                @click="if (!@this.selectedServiceId) { showAlert = true; } else { $wire.call('openWizard', @this.selectedServiceId) }"--}}
{{--                wire:loading.remove wire:target="selectService"--}}
{{--                class="px-4 py-3 bg-primary-600 text-white rounded-lg shadow-sm hover:bg-primary-600 focus:outline-none ">--}}
{{--                {{__('labels.order')}}--}}
{{--            </button>--}}

{{--            <span wire:loading wire:target="selectService"--}}
{{--                  class="px-4 py-3 shadow-sm ">--}}
{{--                <i class="fas fa-spinner fa-spin text-lg md:text-xl text-primary-600 dark:text-primary-600"></i>--}}
{{--            </span>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    <div x-show="showAlert" x-cloak class="mt-2 text-red-500">--}}
{{--        {{__('string.search_service_error')}}--}}
{{--    </div>--}}
{{--</div>--}}
<div>
    <form wire:submit="create">
        {{ $this->form }}
    </form>

    <x-filament-actions::modals />
</div>
