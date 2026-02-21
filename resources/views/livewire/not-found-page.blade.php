<div class="h-screen w-screen flex items-center gap-12 justify-center ">


        <x-image class="h-96" src="/assets/error/404.svg" alt="jad"/>
    <div class="flex flex-col gap-4 items-start">
        <div class="text-8xl">404</div>
        <h3>{{__('string.404-title')}}</h3>
        <p>@lang('string.404-sub-title')</p>
        <x-filament::button
            href="/"
            tag="a"
            size="lg"
            class="mt-12"
        >
            <span class="text-lg">عودة للصفحة الرئيسية</span>
        </x-filament::button>
    </div>
</div>
