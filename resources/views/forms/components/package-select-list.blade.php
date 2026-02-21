@php
    use App\Models\Package;
    use function Filament\Support\prepare_inherited_attributes;

    $gridDirection = $getGridDirection() ?? 'column';
    $id = $getId();
    $isDisabled = $isDisabled();
    $isInline = $isInline();
    $statePath = $getStatePath();
@endphp

<style>
    input[type="radio"]:checked + label {
        border-color: #2547b6;
        background: #b8c9ed;
    }

    input[type="radio"]:checked + label .radio-label::after {
        display: block;
    }
</style>

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <x-filament::grid
        :default="$getColumns('default')"
        :sm="$getColumns('sm')"
        :md="$getColumns('md')"
        :lg="$getColumns('lg')"
        :xl="$getColumns('xl')"
        :two-xl="$getColumns('2xl')"
        :is-grid="!$isInline"
        :direction="$gridDirection"
        :attributes="prepare_inherited_attributes($attributes)
            ->merge($getExtraAttributes(), escape: false)
            ->class([
                'fi-fo-radio gap-4',
                '-mt-4' => (!$isInline) && ($gridDirection === 'column'),
                'flex flex-wrap' => $isInline,
            ])
        ">
        @foreach ($getOptions() as $value => $label)
            @php
                $record = Package::find($value);
            @endphp
            <div @class([
                'break-inside-avoid' => (!$isInline) && ($gridDirection === 'column'),
            ])>
                <div
                    class="contents border-gray-100 border-2 dark:border-1 dark:border-white/10 w-full rounded-lg cursor-pointer dark:z-10"
                    wire:key="{{$id.'_'.$value}}">
                    <input
                        type="radio"
                        id="{{ $id . '-' . $value }}"
                        name="{{ $id }}"
                        value="{{ $value }}"
                        class="absolute opacity-0"
                        {{ $isDisabled || $isOptionDisabled($value, $label) ? 'disabled' : '' }}
                        wire:model="{{ $statePath }}"
                        wire:loading.attr="disabled"
                    />
                    <label for="{{ $id . '-' . $value }}"
                           class="relative rounded-lg shadow-md  dark:border-white/10 cursor-pointer p-4 block border border-gray-200 hover:border-gray-300 transition duration-150 ease-in-out">
                        @if($record->name)
                            <span class="flex gap-2 font-bold text-gray-900 uppercase mb-0 max-w-max  z-20 bg-transparent	dark:z-20 text-xl radio-label -mt-6 ">
                                <x-filament::badge size="lg">{{ $label }}</x-filament::badge>
                                @if($record->is_best_value)
                                    <x-filament::badge color="success" size="md">{{ __('wallet.packages.best_value') }}</x-filament::badge>
                                @endif
                            </span>
                        @endif
                        <div class="flex flex-col md:flex-row justify-between dark:text-white ">
                            <div class="flex flex-col p-2">
                                <span class="text-sm font-light mb-4">{{ $record->description }}</span>
                                <span class="font-bold uppercase mb-2 inline">
                                    <x-icon name="heroicon-o-wallet" class="w-5 h-5 text-gray-600 align-baseline inline"></x-icon>
                                    {{ $record->credits }}
                                    <span class="text-xs text-gray-500  dark:text-gray-300 ">{{__('wallet.credits')}}</span>
                                </span>
                            </div>
                            <div class="flex flex-col items-end p-2  dark:text-white">
                                <div class="text-sm font-semibold text-gray-700    dark:text-white">
                                  {{ $record->getFinalPrice() }}  {{ $record->currency?->symbol }}
                                    <span class="text-xs text-gray-700  dark:text-gray-300">{{ $record->ex_VAT ? "(inc VAT)" : "(ex VAT)" }}</span>
                                </div>
                                <div class="text-xs text-gray-600  dark:text-white">
                                   {{ number_format($record->getFinalPrice()/$record->credits, 2) }}  {{ $record->currency?->symbol }} / {{__('wallet.credits')}}
                                </div>
                            </div>
                        </div>
                        <span aria-hidden="true" class="invisible absolute inset-0 border-2 border-primary rounded-lg focus:visible hover:visible ">
                            <span class="absolute top-0 left-3 h-10 w-10 inline-flex items-center justify-center rounded-full text-primary-400">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 15" fill="primary" class="h-10 w-5 text-primary-500">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        </span>
                    </label>
                    @if ($hasDescription($value))
                        <p class="text-gray-500 dark:text-gray-400">
                            {{ $getDescription($value) }}
                        </p>
                    @endif
                </div>
            </div>
        @endforeach
    </x-filament::grid>
</x-dynamic-component>
