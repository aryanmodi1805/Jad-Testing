<section>
    <!-- Hero Section -->
    <div
         class="relative bg-cover max-md:bg-center h-[50vh]"
         style="background-image: url('{{app(\App\Settings\HeroesSettings::class)->getSubHero()}}');">
        <div
            class="container mx-auto rtl:lg:pr-20 ltr:lg:pl-20 py-20 relative z-10 flex flex-col items-center justify-center h-full text-center">
            <h1 class="text-4xl text-center md:text-5xl font-bold text-gray-700 mb-6">{{ __('string.find_professionals', ['category_name' => $service->name]) }}</h1>
            {{--            <livewire:search :service-id="$service->id" :service-name="$service->name"/>--}}
            <x-filament::button wire:click="placeOrder"  class="text-white bg-secondary-500 focus:ring-4 focus:outline-none focus:ring-[#92298D5f] font-medium rounded-lg text-lg w-52 h-14">
                <span class="text-lg">@lang('services.place_your_order')</span>
            </x-filament::button>
        </div>
    </div>

    <!-- Info Section -->
    <section class="container mx-auto rtl:lg:pr-20 ltr:lg:pl-20 py-16 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6">{{ __('string.help_finding', ['category_name' => $service->name]) }}</h2>
        <p class="text-lg text-gray-700 mb-12">{{ __('string.intro_text', ['category_name' => $service->name]) }}</p>
        <div class="grid gap-8 grid-cols-1 md:grid-cols-3">
            <div class="bg-white p-6  shadow-md">
                <div class="text-2xl font-bold text-primary-500 mb-4">1</div>
                <h3 class="text-xl font-semibold mb-4">{{ __('string.step1_title') }}</h3>
                <p class="text-gray-700">{{ __('string.step1_text', ['category_name' => $service->name]) }}</p>
            </div>
            <div class="bg-white p-6 shadow-md">
                <div class="text-2xl font-bold text-primary-500 mb-4">2</div>
                <h3 class="text-xl font-semibold mb-4">{{ __('string.step2_title') }}</h3>
                <p class="text-gray-700">{{ __('string.step2_text', ['category_name' => $service->name]) }}</p>
            </div>
            <div class="bg-white p-6 shadow-md">
                <div class="text-2xl font-bold text-primary-500 mb-4">3</div>
                <h3 class="text-xl font-semibold mb-4">{{ __('string.step3_title', ['category_name' => $service->name]) }}</h3>
                <p class="text-gray-700">{{ __('string.step3_text', ['category_name' => $service->name]) }}</p>
            </div>
        </div>
    </section>

    <!-- Related Services Section -->

    @unless($relatedServices->isEmpty())
    <x-scrollable-container title="{{ __('services.services.related_services') }}">
        @foreach($relatedServices as $relatedService)
            <div
                class="flex-none m-4 w-[25rem] bg-white rounded-lg overflow-hidden shadow-lg hover:shadow-md transition-shadow duration-300">
                <a href="{{ route('filament.guest.pages.service', $relatedService) }}">
                    <x-image class="w-full h-72 object-cover" src="{{ asset('storage/' . $relatedService->image) }}"
                         alt="{{ $relatedService->name }}"/>
                </a>
                <div class="p-4">
                    <a href="{{ route('filament.guest.pages.service', $relatedService) }}">
                        <h5 class="text-lg font-bold text-gray-900">{{ $relatedService->name }}</h5>
                    </a>
                    <p class="text-sm text-gray-600">{{ $relatedService->short_description }}</p>
                    @if($relatedService->is_online)
                        <span
                            class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mt-2">@lang('labels.available_online')</span>
                    @endif
                </div>
            </div>
        @endforeach

    </x-scrollable-container>
    @endunless

    <!-- Service Reviews Section -->

@unless($reviews->isEmpty())
    <section>
        <div class="container mx-auto rtl:lg:pr-20 ltr:lg:pl-20 py-28">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl md:text-4xl font-bold">@lang('string.reviews')</h2>
                <div class="text-right">
                    <x-rate :rating="$averageRating" :totalReviews="$totalReviews" showRatingText
                            class="mt-1 justify-self-start sm:justify-self-end"/>
                </div>
            </div>
            <div class="grid gap-8 grid-cols-1 md:grid-cols-3">
                @foreach($reviews as $review)
                    <div class="bg-[#f9f9fa] border border-solid border-gray-300 p-6 rounded-lg ">
                        <div class="flex items-center mb-4">
                            {{ \Filament\Infolists\Infolist::make()
                                ->record($review)
                                ->schema([
                                    \Mokhosh\FilamentRating\Entries\RatingEntry::make('rating')
                                        ->state((double)$review->rating)
                                        ->label(false),
                                ])
                                ->render() }}
                        </div>
                        <p class="text-gray-700 mb-4">{{ $review->review }}</p>
                        <p class="text-gray-500 text-sm">{{ $review->created_at->translatedFormat('d M Y') }}</p>
                    </div>

                @endforeach
            </div>
        </div>
    </section>
    @endunless

    <!-- End Service Reviews Section -->
    <livewire:footer/>


</section>
