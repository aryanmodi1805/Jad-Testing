<div class="container px-2 mx-auto mt-6 md:px-4">

    <x-slot name="header">
        <span class="capitalize">{{ $post->title }}</span>
    </x-slot>

    <x-slot name="breadcrumbs">
        @if($post->parent !== null)

            <a  href="{{ route('page',[$post->parent->slug]) }}"  class="text-gray-600 dark:text-gray-200 hover:text-blue-900 hover:underline focus:text-blue-900 focus:underline">
                {{ $post->parent->title }}
            </a>
            <span class="mx-2 text-gray-500 dark:text-gray-300 rtl:-scale-x-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </span>
        @endif
        <a   class="text-primary-600 dark:text-primary-400 " aria-current="page">
            {{ $post->title }}
        </a>

    </x-slot>

    @if($post->image() !== null)
        <x-image alt="{{ $post->title }}" src="{{ $post->image() }}" class="my-10 w-full md:h-96 aspect-video shadow-md rounded-[2rem] rounded-bl-none z-0 object-scale-down"/>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-[2rem] rounded-tl-none shadow-md px-10 pb-6 ">
        <div class="flex items-center justify-between">
            <span class="font-light text-gray-600 dark:text-gray-100">{{ optional($post->published_at)->diffForHumans() ?? '' }}</span>
            <div>
                @unless ($post->tags->isEmpty())
                    @each($skyTheme.'.partial.category', $post->tags->where('type','category'), 'category')
                @endunless
            </div>
        </div>

        <div class="flex flex-col items-start justify-start gap-4">
            <div>
                <a href="#" class="text-2xl font-bold text-gray-700 dark:text-gray-100 hover:underline">
                    {{ $post->title ?? '' }}
                </a>
                <p class="mt-2 text-gray-600 dark:text-gray-200">
                    {{ $post->description ?? '' }}
                </p>
            </div>

        </div>

        <div class="mt-6 prose break-normal lg:mt-12 dark:prose-invert max-w-none">
            {!! $post->getContent() !!}
        </div>

        @if(!$children->isEmpty())
            <div class="flex flex-col gap-4 py-6 mt-4">
                <h1 class="text-xl font-bold text-gray-700 dark:text-gray-100 md:text-2xl">children pages</h1>

                <div class="grid grid-cols-3 gap-4">
                    @foreach($children as $post)
                        @include($skyTheme.'.partial.children-pages')
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
