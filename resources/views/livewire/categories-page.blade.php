<section>
    <!-- Hero Section -->
    <div class="relative bg-cover h-[50vh]" style="background-image: url('{{app(\App\Settings\HeroesSettings::class)->getSubHero()}}');">
        <div
            class="container mx-auto max-w-screen-xl px-4 py-20 relative z-10 flex flex-col items-center justify-center h-full text-center">
            <h1 class="text-4xl text-center md:text-5xl font-bold text-gray-700 mb-6">{{ __('string.find_professionals', ['category_name' => $category->name]) }}</h1>
            <livewire:search :category-id="$category->id"/>
        </div>
    </div>

    <!-- Info Section -->
    <section class="container mx-auto max-w-screen-xl px-4 py-16 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6">{{ __('string.help_finding', ['category_name' => $category->name]) }}</h2>
        <p class="text-lg text-gray-700 mb-12">{{ __('string.intro_text', ['category_name' => $category->name]) }}</p>
        <div class="grid gap-8 grid-cols-1 md:grid-cols-3">
            <div class="bg-white p-6  shadow-md">
                <div class="text-2xl font-bold text-primary-500 mb-4">1</div>
                <h3 class="text-xl font-semibold mb-4">{{ __('string.step1_title') }}</h3>
                <p class="text-gray-700 text-lg">{{ __('string.step1_text', ['category_name' => $category->name]) }}</p>
            </div>
            <div class="bg-white p-6  shadow-md">
                <div class="text-2xl font-bold text-primary-500 mb-4">2</div>
                <h3 class="text-xl font-semibold mb-4">{{ __('string.step2_title') }}</h3>
                <p class="text-gray-700 text-lg">{{ __('string.step2_text', ['category_name' => $category->name]) }}</p>
            </div>
            <div class="bg-white p-6  shadow-md">
                <div class="text-2xl font-bold text-primary-500 mb-4">3</div>
                <h3 class="text-xl font-semibold mb-4">{{ __('string.step3_title', ['category_name' => $category->name]) }}</h3>
                <p class="text-gray-700 text-lg">{{ __('string.step3_text', ['category_name' => $category->name]) }}</p>
            </div>
        </div>
    </section>

    @unless($popularServices->isEmpty())

        <section class="items-center">
            <div class="container mx-auto max-w-screen-xl px-4 py-16">
                <h2 class="text-3xl font-bold mb-12">{{ __('services.services.popular_services') }}</h2>

                <div class="grid gap-8 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 ">
                    @foreach($popularServices as $service)
                        <div
                            class="rounded-lg w-full overflow-hidden shadow-md hover:shadow-xl transition-shadow duration-300">
                            <a href="{{ route('filament.guest.pages.service', $service) }}">
                                <x-image class="rounded-t-lg w-full h-72 object-cover"
                                     src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}"/>
                            </a>
                            <div class="p-4">
                                <a href="{{ route('filament.guest.pages.service', $service) }}">
                                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $service->name }}</h5>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endunless

    <!-- Services Section -->
    @unless($subcategories->isEmpty())
    <section class="items-center p-12">
        <div class="container mx-auto max-w-screen-xl px-4 py-16">
            <h2 class="text-3xl font-bold mb-12">{{ __('services.services.all_services') }}</h2>


            @unless($subcategories->isEmpty())
                @foreach($subcategories as $subcategory)
                    @unless($subcategory->services->isEmpty())
                    <div class="mb-12">
                        <h3 class="text-2xl font-semibold mb-4">{{ $subcategory->name }}</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($subcategory->services as $service)
                                <a href="{{ route('filament.guest.pages.service', $service) }}"
                                   class="mx-2  text-primary-600 border border-primary-500 rounded-lg px-4 py-2 transition hover:bg-primary-500 hover:text-white">
                                    {{ $service->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endunless
                @endforeach

            @endunless
        </div>
    </section>
    @endunless
    <livewire:footer/>
</section>
