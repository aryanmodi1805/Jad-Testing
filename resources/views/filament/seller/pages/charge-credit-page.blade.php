<x-filament-panels::page.simple class="p-0 m-0 px-0 mx-[-19px]">

    <div
        class=" w-full flex flex-col m-0 p-0 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <form wire:submit="charge" class="px-0 m-0 flex flex-col justify-center ">

            {{$this->form}}
            <div class="my-4   border-t-0 w-full"></div>
            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="true"
            />
            <div class="my-12   border-t-0 w-full"></div>

        </form>
    </div>
</x-filament-panels::page.simple>
