<section>
    <div id="how-it-works"
         class="py-[6rem] container h-full mx-auto px-8 flex flex-col justify-center items-center gap-8">

        <h3 class="text-5xl max-sm:text-3xl">{{ __('string.how_it_works') }}</h3>
        <div
            class="flex items-stretch justify-between w-full h-full py-12 max-lg:grid max-lg:justify-center max-lg:grid-cols-1 max-lg:gap-6">
            @for ($i = 1; $i <= 4; $i++)

                <div class="flex flex-col w-full gap-8 items-center">

                    <div class="flex w-3/5 aspect-square rounded-full bg-white justify-center items-center shadow-xl max-xl:w-full max-lg:w-3/5">

                            <div class="relative p-12">
                                <x-image class="object-cover w-full max-h-60 "  src="./assets/how-it-works/{{$i}}.png"
                                         alt="{{$howItWorksCustomer->getStepTitle($i,app()->getLocale())}}"/>
                                <div class="absolute flex top-2 right-2 bg-secondary-500 rounded-full w-10 h-10 justify-center items-center"> <span class="text-white font-bold">0{{$i}}</span>
                                </div>
                            </div>
                    </div>
                    <div class="flex flex-col items-center gap-4 text-center w-full h-full">

                            <h5 class="text-2xl font-bold">{{$howItWorksCustomer->getStepTitle($i,app()->getLocale())}}</h5>
                        <p class="text-gray-500">{{$howItWorksCustomer->getStepDescription($i,app()->getLocale())}}</p>
                    </div>


                </div>

                {{--                    <div--}}
                {{--                        class="grow flex flex-col  items-center justify-start py-8 px-4 gap-14 w-full lg:min-h-full max-lg:h-full border border-[#97979781] rounded-md">--}}
                {{--                        <div class="flex items-center justify-between w-full h-full">--}}
                {{--                            <span--}}
                {{--                                class="bg-gradient-to-r from-[#CE4F57]  to-[#92298D] inline-block text-transparent bg-clip-text">--}}
                {{--                                <p class="text-center text-8xl font-mtb">{{$i}}</p>--}}
                {{--                            </span>--}}
                {{--                            <span class="w-1/5">--}}
                {{--                                <x-image src="./assets/how-it-works/{{$i}}.svg" alt="{{$howItWorksCustomer->getStepTitle($i,app()->getLocale())}}"/>--}}
                {{--                            </span>--}}
                {{--                        </div>--}}
                {{--                        <div class="flex flex-col items-start gap-4 text-start w-full h-full">--}}
                {{--                            <span--}}
                {{--                                class="bg-gradient-to-r from-[#CE4F57]  to-[#92298D] inline-block text-transparent bg-clip-text">--}}

                {{--                                <h5 class="text-2xl ltr:font-mtb rtl:font-noto">{{$howItWorksCustomer->getStepTitle($i,app()->getLocale())}}</h5>--}}
                {{--                            </span>--}}
                {{--                            <p class="text-[#979797]">{{$howItWorksCustomer->getStepDescription($i,app()->getLocale())}}</p>--}}
                {{--                        </div>--}}
                {{--                    </div>--}}

                <div class="flex justify-center h-full w-fit max-lg:w-full {{$i%2 ? 'mt-0 rtl:mt-36':'mt-36 rtl:mt-0'}}" x-show="{{$i}} !== 4">
                    <div class="rtl:rotate-180 max-lg:hidden">
                        @if($i%2)
                            <x-image src="./assets/how-it-works/arrow.svg" alt="arrow"/>
                        @else
                        <x-image src="./assets/how-it-works/arrow-r.svg" alt="arrow"/>
                        @endif
{{--                        <i class="text-4xl ti ti-chevron-right rtl:rotate-180 max-lg:hidden"></i>--}}
                    </div>
                    <i class="text-4xl ti ti-chevron-down lg:hidden"></i>
                </div>

            @endfor
        </div>

    </div>
</section>
