<section class="py-12">

     <h3 class="text-3xl sm:text-4xl text-center font-mtb mb-8">{{ __('string.our_partners') }}</h3>
    <div class=" !h-[20rem] px-4">
     <swiper-container navigation="true" navigation-color="#FF0000" slides-per-view="auto" nested="true" space-between="30" slides-per-group="1" grab-cursor="true"
                      initial-slide="6" loop="true" autoplay-delay="3000" autoplay-pause-on-mouse-enter="true"
                      autoplay-disable-on-interaction="0"  >


        @foreach ($partners as $partner)
            <swiper-slide wire:ignore x-ignore class="swiper-slide !w-[27rem] !p-0 max-sm:!w-[16rem] max-sm:!h-[14rem]">
            <div class=" overflow-hidden border-0 rounded-md">
                <x-image class="object-scale-down w-full h-48" src="{{ asset('storage/' . $partner->image) }}" alt="{{ $partner->name }}"/>
            </div>
            </swiper-slide>
        @endforeach

    </swiper-container>
    </div>
</section>
