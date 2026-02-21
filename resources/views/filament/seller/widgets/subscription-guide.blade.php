<x-filament-widgets::widget>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-full p-0 m-0">
        <x-filament::section >
            <h5 class=" m-0  text-xl   tracking-tight text-gray-900 dark:text-white">{{__('subscriptions.premium.single')}} </h5>

            <div class="flex flex-col  mb-3 font-normal text-gray-700 dark:text-gray-400">
              <span>  {!!html_entity_decode( app(\App\Settings\SubscriptionGuideSettings::class)->getPremiumSubscriptionGuide(app()->getLocale()))!!}
              </span>
{{--                <a href="{{url('/seller/pricing')}}"--}}
{{--                   class="inline-flex   text-primary-500 dark:text-primary-200   px-2 py-2 text-sm font-medium text-center  rounded-lg     ">--}}
{{--                    {{__('string.more')}}--}}
{{--                    <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"--}}
{{--                         fill="none" viewBox="0 0 14 10">--}}
{{--                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"--}}
{{--                              d="M1 5h12m0 0L9 1m4 4L9 9"/>--}}
{{--                    </svg>--}}
{{--                </a>--}}
            </div>



        </x-filament::section>

        <x-filament::section>
            <h5 class=" m-0  text-xl  tracking-tight text-gray-900 dark:text-white">{{__('subscriptions.unlimited_credit_subscription')}} </h5>

            <div class="flex flex-col mb-3 font-normal text-gray-700 dark:text-gray-400">
                {!!html_entity_decode( app(\App\Settings\SubscriptionGuideSettings::class)->getCreditSubscriptionGuide(app()->getLocale()))!!}
{{--                <a href="{{url('/seller/pricing')}}"--}}
{{--                   class="inline-flex items-end text-primary-500 dark:text-primary-200 justify-self-end px-2 py-2 text-sm font-medium text-center  rounded-lg     ">--}}
{{--                    {{__('string.more')}}--}}
{{--                    <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"--}}
{{--                         fill="none" viewBox="0 0 14 10">--}}
{{--                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"--}}
{{--                              d="M1 5h12m0 0L9 1m4 4L9 9"/>--}}
{{--                    </svg>--}}
{{--                </a>--}}
            </div>

        </x-filament::section>
    </div>

</x-filament-widgets::widget>
