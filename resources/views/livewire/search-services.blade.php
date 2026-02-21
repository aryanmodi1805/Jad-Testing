<div x-data="{ show:false }" x-on:click.away="show=false" class="relative">
    <div class="mb-4">
        <x-filament::input.wrapper
            class="border-2 border-primary-500 p-3"
        >
            <x-filament::input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{__('labels.search_for_services')}}"
                x-on:focus="show=true"
            />
        </x-filament::input.wrapper>
    </div>
    <div
        x-show="show"
        class="flex flex-col bg-white overflow-y-auto overflow-x-hidden max-h-96 border absolute w-full py-2">
        @if($services->isNotEmpty())

            @foreach($services as $service)
                <button
                    wire:click="openWizard('{{ $service->id }}')"
                    type="button"
                    class="transform py-2 px-6 transition duration-300 ease-in-out hover:bg-primary-50"
                >
                    <div class="text-3 text-gray-700 text-start">
                        {{ $service->name }}</div>
                </button>

            @endforeach

        @else
            <div class="bg-white p-4 text-center">@lang('services.services.no_services_found')</div>
        @endif
    </div>
</div>
