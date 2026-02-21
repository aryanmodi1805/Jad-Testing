<div>
    <div class="absolute right-0 top-3">
        <img src="/assets/photos/Vector.png">
    </div>

    <main class="grid w-screen h-screen  overflow-auto grid-cols-12">
        <div class="xl:col-span-8 max-xl:hidden">
            <x-image class="object-cover w-full h-full"
                     src="{{app(\App\Settings\AuthSettings::class)->getRegisterPageImage()}}" alt="register photo"/>
        </div>
        <div class="col-span-4 py-[5rem] px-[3.5rem] flex flex-col items-start w-full h-full max-xl:col-span-12">
            <div class="text-center mx-auto mb-3 mt-0"> {{app()->getLocale() == 'ar' ? getArLogo():getEnLogo()}}</div>

            <h4 class="text-3xl text-start">
                {{$this->getScope() == 'seller'? __('auth.register_as_pro') : __('auth.register_as_customer')}}
            </h4>


            <form wire:submit="register" class="flex flex-col justify-center w-full gap-4 z-10">
                <br class="w-full h-fit">
                {{$this->form}}

                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />

                <div class="my-4 border border-t-0"></div>

                <span class="text-center">@lang('auth.have_account') <a
                        class="bg-secondary-500 inline-block text-transparent bg-clip-text"
                        href="./login">@lang('auth.login_now')</a>
                </span>

            </form>
        </div>

    </main>
</div>




