<section class="bg-center  w-full " style="background-image: url('{{ asset('assets/customers/reviews-bg.png') }}');">
    <div class="container mx-auto px-4 sm:px-8 py-12 sm:py-24" x-data="{ selectedReview: 0 }">
        <div class="flex flex-col items-center w-full gap-2">
            <div class="flex flex-col gap-2 items-center">
                <div class="text-xl text-primary-500 font-medium">@lang('string.testimonials')</div>
                <h3 class="text-3xl sm:text-5xl text-center mb-8 sm:mb-12">{{ __('string.customer_opinions') }}</h3>
            </div>
            <div class="w-full h-full px-60 max-lg:px-0 py-4">
                <swiper-container slides-per-view="auto" pagination="true" nested="true" space-between="30"
                                  slides-per-group="1"
                                  grab-cursor="true"
                                  speed="1500"
                                  initial-slide="6" loop="true" autoplay-delay="3000"
                                  autoplay-pause-on-mouse-enter="true"
                                  autoplay-disable-on-interaction="0">
                    @foreach ($siteReviews as $index => $review)
                        <swiper-slide class="min-h-full py-12">
                            <div
                                class="relative flex flex-col items-center gap-8  min-h-36 text-start ">
                                <figure class="max-w-screen-sm mx-auto text-center">
                                    <svg class="w-10 h-10 rotate-180 mx-auto mb-3 text-gray-400 dark:text-gray-600"
                                         aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                         viewBox="0 0 18 14">
                                        <path
                                            d="M6 0H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h4v1a3 3 0 0 1-3 3H2a1 1 0 0 0 0 2h1a5.006 5.006 0 0 0 5-5V2a2 2 0 0 0-2-2Zm10 0h-4a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h4v1a3 3 0 0 1-3 3h-1a1 1 0 0 0 0 2h1a5.006 5.006 0 0 0 5-5V2a2 2 0 0 0-2-2Z"/>
                                    </svg>
                                    <blockquote>
                                        <p class="text-lg italic font-medium text-gray-600 dark:text-white">{{$review->review}}</p>
                                    </blockquote>
                                    <figcaption
                                        class="flex items-center justify-center my-6  space-x-3 rtl:space-x-reverse">
                                        <x-image class="w-16 h-16 rounded-full"
                                                 src="{{$review->rater->avatar_url != null ? Storage::url($review->rater->avatar_url) : '/assets/profile-empty-state.webp'  }}"
                                                 alt="profile picture"/>
                                        <div
                                            class="flex flex-col items-start ">
                                            <cite
                                                class="pe-3 font-medium text-lg text-gray-900 dark:text-white">{{$review->rater->name}}</cite>

                                            <cite
                                                class="text-sm text-gray-500 dark:text-gray-400">{{$review->rater_type == App\Models\Customer::class ? __('string.jad_customer'):__('string.jad_seller')}}</cite>
                                        </div>
                                    </figcaption>
                                </figure>
                            </div>
                        </swiper-slide>
                    @endforeach


                </swiper-container>
            </div>
        </div>
    </div>

</section>
