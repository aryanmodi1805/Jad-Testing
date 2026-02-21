<div>
    <div class="absolute right-0 top-3">

        <img src="/assets/photos/Vector.png">
    </div>

    <main class="grid min-h-screen w-full grid-cols-12 overflow-hidden bg-white max-xl:grid-cols-1">
        <div class="xl:col-span-8 hidden xl:block">
            <x-image class="object-cover w-full h-full"
                     src="{{app(\App\Settings\AuthSettings::class)->getLoginPageImage()}}" alt="login photo"/>
        </div>
        <div class="col-span-12 xl:col-span-4 flex w-full items-start justify-center px-6 py-10 max-md:px-4 max-md:py-8">
          <div class="w-full max-w-md">
            <div class="text-center mx-auto mb-3 mt-0"> {{  app()->getLocale() == 'ar' ? getArLogo():getEnLogo()}}</div>
            <h4 class="text-3xl text-start">
                @lang('auth.login-header')
            </h4>


            <form wire:submit="authenticate" class="flex flex-col justify-center w-full gap-4 z-10">
                <br class="w-full h-fit">
                <h4 class="text-2xl mt-6 text-start">
                    {{$this->getScope() == 'seller'? __('auth.login_as_pro') : __('auth.login_as_customer')}}
                </h4>

                {{$this->form}}

                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />

                <div class="my-4 border border-t-0"></div>

                <span class="text-center">@lang('auth.donot_have_account') <a
                        class="bg-secondary-500 inline-block text-transparent bg-clip-text"
                        href="./register">@lang('auth.sign_up_now')</a></span>

            </form>
          </div>
        </div>

    </main>
</div>




