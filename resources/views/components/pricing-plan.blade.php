{{--@props(['plan', 'color'])--}}
@php
    $color=[];
    $plan=$getRecord();
         $color_degree = $getRowLoop()->first ?0:($getRowLoop()->index +4) * 100;
    $btnKey= Str::random().'X'.$plan->id;

 $record = $getRecord();
@endphp

<div class="flex shrink-0 items-center gap-4 flex-wrap sm:flex-nowrap justify-start ps-4 pe-4a">
    <div
        class="
        rounded-tr-[3.5rem] rounded-br-[1.5rem] rounded-bl-[1.5rem] bg-transparent p-0 pt-6
           shadow-md  border-1 border-gray-300 text-center text-primary-{{$color_degree}} max-w-[350px]
         ">
        <div class="mb-6">
            <div class="h-4">
                @if( $record->subscriptions_count > 0)
                    <x-filament::badge color="info" size="md"
                                       class="  mx-3 px-1  rounded-full hover:bg-gray-100 transition">
                        {{__('subscriptions.already_subscribed')}} ( {{ $record->subscriptions_count}})
                    </x-filament::badge>
                @endif
            </div>
            <h3 class="text-xl font-bold py-1 border-b border-{{$color_degree }}">{{ $plan->name }}</h3>
        </div>
        <div class="mb-4">
            <h2 class="text-xl font-extrabold">{{ $plan->getFinalPrice() }} <span>{{$plan->currency?->symbol ?? getCurrencySample()}}</span>
                / {{ $plan->billing_cycles == 1 ? __('subscriptions.yearly') : __('subscriptions.monthly')}}</h2>
            <span class="font-light text-xs text-gray-700 dark:text-gray-300 max-w-[300px] h-8">
                {{__("wallet.packages.inc (VAT)", ['p' => (\Filament\Facades\Filament::getTenant())->vat_percentage ?? 0]) }}
{{--                {{ $plan->ex_VAT ? __("wallet.packages.inc (VAT)", ['p' => (\Filament\Facades\Filament::getTenant())->vat_percentage ?? 0]) :  __("wallet.packages.ex (VAT)", ['p' => (\Filament\Facades\Filament::getTenant())->vat_percentage ?? 0]) }}--}}
            </span>
            <p class="font-light text-sm text-gray-700 dark:text-gray-300 max-w-[300px] h-8">{{ $plan->description }}</p>

        </div>

        <div
            class="
            rounded-tl-[3.5rem] rounded-br-[1.5rem] p-4
             bg-gray-300

            shadow-lg text-center mr-[-6px] justify-center w-full flex
            flex-col text-gray-900 min-w-[320px] max-w-[300px] min-h-[220px]
{{--             bg-primary-{{$color_degree}}--}}
             "
            style="background-color:{{$plan->bg_color}};  color: {{$plan->text_color}}">
            <ul class="mb-3  mt-4">


                <li class="py-2 my-1 flex  border-b  border-gray-600">
                    @if($plan->is_premium)
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-6 w-6  rounded-3xl bg-white text-success-700    me-1 p-0 "/>

                   {{__('subscriptions.premium.unlimited') }}
                       {{-- {{
                        __('subscriptions.premium.premium_plan_title_for_number_of_x',[
                            'limit'=> $plan->premium_items_limit == -1? __('subscriptions.features.Unlimited') :$plan->premium_items_limit ,
                                'x'=>$plan->premium_type?->getSingleLabel()
                                ])
                         }}--}}
                    @else
                        <x-filament::icon icon="heroicon-o-x-circle" class="h-6 w-6  rounded-3xl bg-white text-red-500   me-1 p-0 "/>
                        {{ __('subscriptions.premium.single') }}
                    @endif

                </li>


                <li class="py-1 flex   ">
                    @if($plan->is_in_credit)
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-6 w-6  rounded-3xl bg-white text-success-700   me-1 p-0 "/>
                        {{__('subscriptions.unlimited_credit_subscription')}}
                        {{--{{
                       __('subscriptions.premium.unlimited_credit_plan_title_for_number_of_x',[
                           'limit'=> $plan->credit_items_limit == -1? __('subscriptions.features.Unlimited') :$plan->credit_items_limit ,
                               'x'=>$plan->credit_type?->getSingleLabel()
                               ])
                        }}--}}
                    @else
                        <x-filament::icon icon="heroicon-o-x-circle" class="h-6 w-6  rounded-3xl bg-white text-red-500   me-1 p-0 "/>
                        {{__('subscriptions.unlimited_credit_subscription')}}
                    @endif
                </li>


            </ul>
            <div class="flex-auto"></div>

        </div>

    </div>
</div>

