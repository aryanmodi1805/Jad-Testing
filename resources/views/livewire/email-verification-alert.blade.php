<div>

    @if(!auth($this->guard)->user()?->email_verified_at)
        <div
            class="flex items-center p-3 mb-2 mt-2 text-sm text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300 dark:border-yellow-800"
            role="alert">
            <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                 fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <span class="sr-only">Info</span>
            <div class="sm:flex ms-3 text-sm font-medium ">
                {{__('otp.mail.alert_text',['name'=>auth($this->guard)->user()->name])}}

                <div wire:click="resend">
                    <x-filament::link
                        :href="$this->url"
                        class="font-semibold underline btn-primary mt-2 sm:mt-0 sm:mx-2 hover:no-underline"
                        weight="semibold"
                    >
                        {{__('Verify Email Address')}}
                    </x-filament::link>
                </div>
                .
            </div>
        </div>

    @endif
        @if($expiredSubscriptions >0 && $guard === 'seller')
            <div class="flex items-center p-4 mb-4 mt-2 text-sm text-red-800 border border-red-300 bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800" role="alert">

            <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                 fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <span class="sr-only">Info</span>
            <div class="sm:flex ms-3 text-sm font-medium ">
                {{__('subscriptions.notification_subscription_expiry_notice_body',['count'=>$this->expiredSubscriptions])}}

                <div wire:click="resend">
                    <x-filament::link
                        :href="\App\Filament\Seller\Clusters\Settings\Resources\SubscriptionResource::getUrl('index').'?tableFilters[is_expired][isActive]=true'"
                        class="font-semibold underline btn-primary mt-2 sm:mt-0 sm:mx-2 hover:no-underline"
                        weight="semibold"
                    >
                        {{__('subscriptions.renew_action')}}
                    </x-filament::link>
                </div>
                .
            </div>
        </div>

    @endif
</div>
