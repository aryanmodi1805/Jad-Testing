<div>
    <x-filament::dropdown>
        <x-slot name="trigger">
            <button
                class="min-w-44 flex gap-4 items-center justify-between  border border-1 border-gray-300 px-4 py-2 text-sm text-white  font-medium  rounded-md hover:text-primary-100  md:me-0 focus:ring-4 focus:ring-gray-100 "
                type="button">
<span class="flex gap-4 items-center justify-start ">
                    <x-icon class="z-10 h-6" name="flag-country-{{ strtolower($currentCountry->code) }}"/>
                {{$currentCountry->name}}
</span>
                <i class="ti ti-chevron-down"></i>
            </button>
        </x-slot>
        <x-filament::dropdown.list>
            @foreach($countries as $country)
                <x-filament::dropdown.list.item href="{{'https://'.strtolower($country->code).'.'.getHost()}}" tag="a">
                    <span class="flex gap-4 items-center justify-start ">
                        <x-icon class="z-10 h-6" name="flag-country-{{ strtolower($country->code) }}"/>
                        {{$country->name}}
                    </span>
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>

</div>
