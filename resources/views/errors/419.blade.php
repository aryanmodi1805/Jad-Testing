<div class="h-screen w-screen flex items-center gap-12 justify-center ">


    <x-image class="h-96" src="/assets/error/404.svg" alt="jad"/>
    <div class="flex flex-col gap-4 items-start">
        <div class="text-8xl">419</div>
        <h3>{{__('string.419-title')}}</h3>
        <x-filament::button
            href="javascript:location.reload()"
            tag="a"
            size="lg"
            class="mt-12"
        >
            <span class="text-lg">إعادة التحميل</span>
        </x-filament::button>
    </div>
</div>
