<section class=" py-24 flex items-center">
    <div class="flex flex-col items-center justify-center gap-8 mx-auto w-full h-full">
        <h3 class="text-5xl font-bold max-sm:text-3xl">{{__('services.our_categories')}}</h3>

        <div class=" container flex-col items-center justify-center gap-8 mx-auto">


            <swiper-container navigation="true" navigation-color="#FF0000" slides-per-view="auto" nested="true"
                              space-between="50" slides-per-group="1" grab-cursor="true"
                              initial-slide="6" loop="true" autoplay-delay="3000" autoplay-pause-on-mouse-enter="true"
                              autoplay-disable-on-interaction="0">
                @foreach($categories as $index => $category)
                    <swiper-slide wire:ignore x-ignore
                                  class="swiper-slide !w-[18rem] !h-[18rem] !p-4 max-sm:!w-[14rem] max-sm:!h-[14rem] !py-8">
                        <a href="{{ route('filament.guest.pages.categories', $category) }}"
                            class="w-full h-full bg-white shadow-xl flex flex-col gap-4 items-center justify-center p-6 hover:bg-primary-50 rounded-md">
                            @if($category->image)
                                <div
                                    class="flex items-center justify-center w-24 h-24  max-sm:w-20 max-sm:h-20"
                                >
                                    <x-image class="object-fill w-20 h-20 max-sm:w-16 max-sm:h-16"
                                             src="{{ Storage::url($category->image) }}"
                                             alt="{{ $category->name }}"
                                    />
                                </div>

                                @elseif($category->icon)
                                    <div
                                        class="flex items-center justify-center w-24 h-24 transition-all duration-300 ease-in-out "
                                    >
                                        <x-icon name="{{ $category->icon }}"
                                                class="w-10 h-10 max-sm:w-8 max-sm:h-8"
                                        />
                                    </div>
                            @else
                                <div
                                    class="flex items-center justify-center w-24 h-24 transition-all duration-300 ease-in-out "
                                >
                                    <x-icon name="tabler-dots"
                                            class="w-8 h-8 transition-all duration-300 ease-in-out max-sm:w-6 max-sm:h-6"
                                    />
                                </div>
                            @endif

                            <p class="text-xl text-center font-bold max-sm:text-sm">
                                {{ $category->name }}
                            </p>


                        </a>
                    </swiper-slide>

                @endforeach

            </swiper-container>
        </div>


        {{--        <div class="flex flex-wrap justify-center items-start w-full gap-8 py-12 place-items-center max-sm:grid max-sm:grid-cols-2 max-sm:w-4/6">--}}
        {{--            @foreach($categories as $index => $category)--}}
        {{--                @php--}}
        {{--                    $colors = ['#007BFF', '#6610F2', '#28A745', '#83257f', '#DC3545', '#6C757D', '#17A2B8'];--}}
        {{--                    $baseColor = $colors[$index % count($colors)];--}}
        {{--                @endphp--}}
        {{--                <a href="{{ route('filament.guest.pages.categories', $category) }}"--}}
        {{--                   class="flex flex-col items-center justify-start gap-4 w-36 group"--}}
        {{--                   style="--base-color: {{ $baseColor }};">--}}
        {{--                    @if($category->icon)--}}
        {{--                        <div--}}
        {{--                            class="flex items-center justify-center w-16 h-16 transition-all duration-300 ease-in-out rounded-full bg-white group-hover:bg-[var(--base-color)] group-hover:shadow-lg"--}}
        {{--                            style="background-color: {{ $baseColor }}1a;">--}}
        {{--                            <x-icon name="{{ $category->icon }}"--}}
        {{--                                    class="w-8 h-8 transition-all duration-300 ease-in-out group-hover:text-white"--}}
        {{--                                    style="color: {{ $baseColor }}80;"/>--}}
        {{--                        </div>--}}
        {{--                    @else--}}
        {{--                        <div--}}
        {{--                            class="flex items-center justify-center w-16 h-16 transition-all duration-300 ease-in-out rounded-full bg-white group-hover:bg-[var(--base-color)] group-hover:shadow-lg"--}}
        {{--                            style="background-color: {{ $baseColor }}1a;">--}}
        {{--                            <x-icon name="tabler-dots"--}}
        {{--                                    class="w-8 h-8 transition-all duration-300 ease-in-out group-hover:text-white"--}}
        {{--                                    style="color: {{ $baseColor }}80;"/>--}}
        {{--                        </div>--}}
        {{--                    @endif--}}
        {{--                    <span class="text-center">--}}
        {{--                    <p class="text-xl font-mtb transition-all duration-300 ease-in-out group-hover:text-[var(--base-color)] group-hover:underline"--}}
        {{--                       style="color: {{ $baseColor }}b3;">--}}
        {{--                        {{ $category->name }}--}}
        {{--                    </p>--}}
        {{--                </span>--}}
        {{--                </a>--}}
        {{--            @endforeach--}}
        {{--        </div>--}}
    </div>
</section>
