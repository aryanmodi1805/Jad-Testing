<section id="services" class="py-8">
    <div class="w-full flex justify-center py-12">
        <h3 class="text-5xl font-bold max-sm:text-3xl">{{__('services.services.popular_services')}}</h3>
    </div>


    <div class="min-h-[20rem]">
        <swiper-container navigation="true" navigation-color="#FF0000" slides-per-view="auto" nested="true"
                          space-between="30" slides-per-group="1" grab-cursor="true"
                          initial-slide="6" loop="true" autoplay-delay="3000" autoplay-pause-on-mouse-enter="true"
                          autoplay-disable-on-interaction="0">
            @foreach($services as $index => $service)
                <swiper-slide wire:ignore x-ignore
                              class="swiper-slide !w-[15rem] !h-[25.5rem] !p-4 max-sm:!w-[12rem] max-sm:!h-[20.25rem]">
                    <div
                        class=" !w-[15rem] !h-[21rem] max-sm:!w-[12rem] max-sm:!h-[16.5rem] {{$index%2 ? 'mt-[3rem]':'mb-[3rem]'}}"
                        onclick="window.openService('{{ $service->id }}');">
                        <div
                            class="flex flex-col justify-start w-full h-full gap-2 transition-transform transform hover:scale-105 cursor-pointer rounded-md overflow-hidden relative">
                            <div
                                class="absolute z-20 px-4  w-full h-full bg-gradient-to-t from-gray-700 from-10% via-transparent via-30%  to-transparent ">
                                <p class="absolute text-white text-xl bottom-5 w-full text-center">{{$service->name}}</p>
                            </div>
                            <div class="relative w-full h-full overflow-hidden rounded-md">
                                <x-image class="object-cover w-full h-full" src="{{ Storage::url($service->image) }}"
                                         alt=""/>

                                @if($service->is_remote)
                                    <h4 class="absolute top-2 left-2 bg-blue-700 text-white px-3 py-1 rounded-lg shadow-md text-xl max-sm:text-sm">{{__('labels.available_online')}}</h4>
                                @endif
                                {{--                                <h4 class="absolute bottom-5 left-2 bg-primary-600 text-white px-3 py-1 rounded-lg shadow-md text-xl max-sm:text-sm">{{$service->name}}</h4>--}}
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75"
                                 wire:loading wire:target="remove('{{$service->id}}')">
                                <div
                                    class="w-10 h-10 border-4 border-t-transparent border-blue-500 rounded-full animate-spin"></div>
                            </div>
                        </div>
                    </div>
                </swiper-slide>
            @endforeach
        </swiper-container>

        @script
        <script>
            window.openService = function (serviceId) {
                $dispatch('open-wizard', {serviceId: serviceId});
            }

        </script>
        @endscript

    </div>


</section>
