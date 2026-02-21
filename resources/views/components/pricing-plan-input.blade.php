@php use App\Filament\Actions\SubscribeNowAction; @endphp
@props(['plans', 'color'])
@php
    $color_degree = 300 ;
@endphp

<div
    class="grid justify-items-center justify-center w-full grid-cols-1
     md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4
         gap-4
        ">
    @foreach($plans as $plan)
        @php
            $color= $this->getPlanColor($plan->name);
            $color_degree += $loop->first ? 0 : 100;
        $btnKey= Str::random().'X'.$plan->id;
        @endphp
        <div
            class="rounded-tr-[3.5rem] rounded-bl-[1.5rem]
                   bg-transparent   p-0 pt-6
                  shadow-[0_10px_50px_-18px_#d761d0]  border-1 border-gray-300
                  text-center  {{  $loop->first? '' :'text-primary-'.$color_degree  }}
                 max-w-[300px]">
            <div class="mb-6">
                <h3 class="text-xl font-bold py-1 border-b border-{{$color_degree }}">{{ $plan->name }}</h3>
                <p class="font-light text-gray-700">{{ $plan->description }}</p>
            </div>
            <div class="mb-4">
                <h2 class="text-2xl font-extrabold">{{ $plan->month_price }} <span>{{$plan->currency?->symbol}}</span>
                    / {{__('subscriptions.monthly')}}</h2>
                {{--  <h4 class="text-lg font-semibold">{{ $plan->year_price }}{{$plan->currency?->symbol}}
                      / {{__('subscriptions.yearly')}}</h4>--}}
            </div>

            <div
                class="rounded-tl-[3.5rem] rounded-br-[1.5rem] p-4  shadow-lg text-center  mr-[-6px]  justify-center bg-gray-500
                    {{  $loop->first? 'bg-secondary-'.$color_degree :'bg-primary-'.$color_degree  }}
                     w-full  flex flex-col  text-white   min-w-[300px] min-h-[250px] ">
                <ul class="mb-6 text-white mt-4">
                    {{--  <li class="py-1 flex items-center justify-center border-b border-white">
                          <svg class="w-5 h-5 mr-2 {{ $color['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                              <path
                                  d="M16.707 4.707l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 10.586l7.293-7.293a1 1 0 011.414 1.414z"></path>
                          </svg>
                          {{   __('subscriptions.features.limited Credits', ['limit' => $plan->credit_limit==0? __('subscriptions.features.limit_text_0') :$plan->credit_limit])}}
                      </li>--}}
                    @if($plan->is_in_main_category)

                        <li class="py-1 flex items-center justify-center border-b border-white">
                            <svg class="w-5 h-5 mr-2 {{ $color['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M16.707 4.707l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 10.586l7.293-7.293a1 1 0 011.414 1.414z"></path>
                            </svg>
                            {{   __('subscriptions.features.In Main Categories', ['limit' => $plan->main_category_limit==0? __('subscriptions.features.limit_text_0') :$plan->main_category_limit])}}
                        </li>
                    @endif
                    @if($plan->is_in_sub_category)
                        <li class="py-1 flex items-center justify-center border-b border-white">
                            <svg class="w-5 h-5 mr-2 {{ $color['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M16.707 4.707l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 10.586l7.293-7.293a1 1 0 011.414 1.414z"></path>
                            </svg>
                            {{   __('subscriptions.features.In Sub Categories', ['limit' => $plan->sub_category_limit==0? __('subscriptions.features.limit_text_0') :$plan->sub_category_limit])}}
                        </li>
                    @endif
                    @if($plan->is_in_service)
                        <li class="py-1 flex items-center justify-center border-b border-white">
                            <svg class="w-5 h-5 mr-2 {{ $color['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M16.707 4.707l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 10.586l7.293-7.293a1 1 0 011.414 1.414z"></path>
                            </svg>
                            {{   __('subscriptions.features.In Services', ['limit' => $plan->service_limit==0? __('subscriptions.features.limit_text_0') :$plan->service_limit])}}
                        </li>
                    @endif
                    {{--   @foreach($plan->features as $feature)
                       <li class="py-1 flex items-center justify-center border-b border-white">
                           <svg class="w-5 h-5 mr-2 {{ $color['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                               <path
                                   d="M16.707 4.707l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 10.586l7.293-7.293a1 1 0 011.414 1.414z"></path>
                           </svg>

                       </li>
                       @endforeach--}}

                </ul>
                <div class="flex-auto"></div>
                <div>
                    @if( $plan->is_subscribed)

                    <x-filament::badge
                        class="  px-6 py-2 rounded-full hover:bg-gray-100 transition">
                        {{__('subscriptions.already_subscribed')}}
                    </x-filament::badge>
                    @else
                    {{ ($this->subscribeAction)(['arg0' => $plan->id,'arg1' => $btnKey]) }}
                    @endif
                </div>
            </div>

        </div>
    @endforeach
</div>

