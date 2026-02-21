<x-filament::page>
    <div class="space-y-1 mx-3 divide-y  divide-gray-900/10 dark:divide-white/10 pb-20">


                    <form wire:submit.prevent="submit" class="space-y-2">

                        {{ $this->form }}

{{--                        <div class="text-right">--}}
{{--                            <x-filament::button type="submit" form="submit" class="align-right">--}}
{{--                                {{ __('filament-breezy::default.profile.personal_info.submit.label') }}--}}
{{--                            </x-filament::button>--}}
{{--                        </div>--}}
                    </form>



    </div>
</x-filament::page>
