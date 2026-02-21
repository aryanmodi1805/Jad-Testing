<div class="flex flex-col" xmlns:livewire="http://www.w3.org/1999/html">


    <div
        class=" flex flex-col gap-4 py-32 items-center justify-center max-sm:justify-center w-full h-full">
        <h1 class="text-6xl text-center  text-gray-800 font-bold leading-normal max-sm:!text-5xl ">{!! __('string.pricing') !!}</h1>
        <h3 class="text-xl text-center px-6  text-gray-500 font-medium leading-normal max-sm:!text-lg ">{!! __('string.pricing-header-content') !!}</h3>

        @if($is_seller_longed_in===false)
            <a href="/seller"
               class="text-white bg-gradient-to-r from-primary-500  to-secondary-500 font-medium rounded-lg text-lg px-4 py-2">
                @lang('string.join-as-seller')
            </a>

        @endif
    </div>


    <livewire:pricing-body/>


    <section id="qa" class="py-16 ">
        <h2 class="text-3xl py-8 text-center font-semibold mb-4">@lang('seller.faqs.plural')</h2>
        <div class="space-y-4 container mx-auto">
            @foreach($faqs as $faq)

                <div x-data="{show:false}" class="bg-white p-4">
                    <button class="flex justify-between items-center w-full focus:outline-none" @click="show=!show">
                        <span class="text-xl ">{{$faq->question}}</span>
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="show" id="qa1" class="mt-4 text-lg text-gray-500">
                        {!! $faq->answer !!}
                    </div>
                </div>
            @endforeach

        </div>
    </section>

    <section>
        @if($is_seller_longed_in===false)
            <div
                class="container mx-auto flex flex-col gap-12 py-24 items-center justify-center max-sm:justify-center w-full h-full ">
                <h1 class="text-4xl text-center  text-gray-800 font-bold leading-normal max-sm:!text-4xl ">{!! __('seller.start-wining') !!}</h1>

                <a href="/seller/register"
                   class="text-white bg-gradient-to-r from-primary-500 to-secondary-500 font-medium rounded-lg text-lg px-4 py-2">
                    @lang('string.join-as-seller')
                </a>


            </div>
        @endif
    </section>

    <livewire:footer/>


</div>
