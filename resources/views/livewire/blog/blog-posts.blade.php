<div class="container px-8 py-12 mx-auto">

    <div class="flex flex-col items-center w-full gap-12">
        <h3 class="text-4xl text-center max-sm:text-3xl w-full">@lang('blogs.blog-posts')</h3>
        <div class="grid items-start grid-cols-3 w-full gap-6 py-12 max-lg:grid-cols-2 max-md:grid-cols-1">

            @foreach ($posts as $post)
                <a href="/blog/post/{{$post->slug}}" class="flex flex-col items-center w-full  ">
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

        {{$posts->links()}}
    </div>
</div>
