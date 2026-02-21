<div class="relative  min-h-screen bg-[#f9f9fa]">

    <div class="absolute bg-center w-full h-[50vh] max-md:bg-center" style="background-image: url('{{app(\App\Settings\HeroesSettings::class)->getSubHero()}}');">

    </div>
    <div class="flex flex-col gap-20 p-6 items-center">
        <div class="container p-6 bg-white  overflow-hidden min-h-[40rem] z-10 mt-24">

           @unless($post->image() === null)
                <div class="w-full overflow-hidden h-[35rem] max-md:h-[25rem] max-xl:h-[35rem] max-sm:h-[20rem]">
                    <x-image class="object-scale-down w-full h-full" src="{{ $post->image() ?? '/assets/blog/post.png' }}" alt="{{ $post->title }}"/>
                </div>
            @endunless
            <div class="flex flex-col gap-2 p-4">

                <div class="flex gap-4">
                            <span
                                class="bg-gradient-to-r from-[#CE4F57]  to-[#92298D] inline-block text-transparent bg-clip-text">
                                <p class="text-sm uppercase">{{ date('d-m-Y', strtotime($post->published_at)) }}</p>
                            </span>

                    <span
                        class="bg-gradient-to-r from-[#CE4F57] to-[#92298D] inline-block text-transparent bg-clip-text">
                                <i class="text-sm ti ti-point"></i>
                            </span>

                    <span
                        class="bg-gradient-to-r from-[#CE4F57]  to-[#92298D] inline-block text-transparent bg-clip-text">
                                <p class="text-sm uppercase">{{ $post?->tag?->name }}</p>
                            </span>

                </div>
                <h1 class=" text-4xl text-start py-4 l font-bold leading-normal max-sm:!text-3xl dark:text-white">{{$post->title}}</h1>


                <div class="mt-6 lg:mt-12 prose dark:prose-invert max-w-none">
                    {!! $post->getContent() !!}
                </div>


            </div>

        </div>

        @unless($related->isEmpty())
        <div class="container flex flex-col justify-start gap-4">
            <h3 class="text-4xl !text-start max-sm:text-3xl w-full">@lang('blogs.related_posts')</h3>
            <div class="grid items-start grid-cols-3 w-full gap-6 py-12 max-md:grid-cols-1">
                @foreach ($related as $post)
                    <a href="/blog/post/{{$post->slug}}" class="flex flex-col items-center w-full ">
                        <div class="w-full top-0 start-0 min-h-full rounded-xl overflow-hidden">
                            <x-image class="object-cover w-full max-h-60"
                                     src="{{ $post->getFirstMediaUrl('posts','thumb') ?? '/assets/blog/post.png' }}"
                                     alt="{{ $post->title }}"/>
                        </div>
                        <div
                            class="flex flex-col items-start gap-2 p-4 bg-white shadow-xl w-5/6 z-10 bottom-0 -mt-10 rounded-xl">
                            <div class="flex gap-4">
                            <span
                                class="bg-gradient-to-r from-primary-500 to-secondary-500 inline-block text-transparent bg-clip-text">
                                <p class="text-sm uppercase">{{ date('d-m-Y', strtotime($post->published_at)) }}</p>
                            </span>

                                <span
                                    class="bg-gradient-to-r from-primary-500 to-secondary-500 inline-block text-transparent bg-clip-text">
                                <i class="text-sm ti ti-point"></i>
                            </span>

                                <span
                                    class="bg-gradient-to-r from-primary-500 to-secondary-500 inline-block text-transparent bg-clip-text">
                                <p class="text-sm uppercase">{{ $post?->tag?->name }}</p>
                            </span>

                            </div>


                            <div class="w-full flex flex-col gap-6 items-start">
                                <h5 class="text-lg w-full font-bold text-lg overflow-hidden">  {{ Str::limit($post->title) }}</h5>
                                <div
                                    class="text-lg font-normal flex gap-2 items-center text-primary-500 uppercase hover:underline">

                                    @lang('blogs.read_more')
                                    <span class="rtl:hidden">
                                        <i class="ti ti-arrow-right"></i>
                                        </span>

                                    <span class="ltr:hidden">
                                            <i class="ti ti-arrow-left"></i>
                                        </span>

                                </div>

                            </div>
                        </div>
                    </a>
                @endforeach


            </div>

        </div>
        @endunless
    </div>
    <livewire:footer/>

</div>
