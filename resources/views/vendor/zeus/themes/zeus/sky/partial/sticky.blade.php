<div class="flex sm:space-x-2 rtl:space-x-reverse px-2 lg:p-4 @if($loop->first) sm:col-span-2 @endif w-full">
    <a class="mb-4 md:mb-0 w-full relative h-[16em] sm:h-[20em] md:h-[22em] lg:h-[24em]" href="{{ route('post',$post->slug) }}">
        <div class="absolute inset-0 w-full h-full z-10 shadow-md rounded-[2rem] @if($loop->first) md:ltr:rounded-br-none md:rtl:rounded-bl-none @else md:ltr:rounded-bl-none md:rtl:rounded-br-none @endif bg-gradient-to-b from-transparent to-gray-700"></div>

        @if($post->image() !== null)
            <x-image alt="{{ $post->title }}" src="{{ $post->image() }}" class="absolute ltr:left-0 rtl:right-0 top-0 w-full h-full shadow-md rounded-[2rem] @if($loop->first) md:ltr:rounded-br-none md:rtl:rounded-bl-none @else md:ltr:rounded-bl-none md:rtl:rounded-br-none @endif z-0 object-cover"/>
        @endif

        <div class="absolute bottom-0 z-20 p-4 ltr:left-0 rtl:right-0">
            <h2 class="text-2xl font-semibold leading-tight text-gray-100 lg:text-4xl">
                {{ $post->title ?? '' }}
            </h2>
            <div class="flex mt-3">
                <div>
                    <p class="text-xs font-semibold text-gray-400">{{ optional($post->published_at)->diffForHumans() ?? '' }}</p>
                </div>
            </div>
        </div>
    </a>
</div>
