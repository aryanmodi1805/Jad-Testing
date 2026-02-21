<article class="mt-6" itemscope itemtype="https://schema.org/Movie">
    <div class="px-6 pb-6 mx-auto bg-white dark:bg-gray-800 rounded-[2rem] rounded-bl-none rounded-tr-none shadow-md">
        <div class="flex items-center justify-between">
            <span class="mt-2 text-sm font-light text-gray-600 dark:text-gray-200">{{ optional($post->published_at)->diffForHumans() ?? '' }}</span>
            <div>
                @unless ($post->tags->isEmpty())
                    @each($skyTheme.'.partial.category', $post->tags->where('type','category'), 'category')
                @endunless
            </div>
        </div>
        <aside class="mt-2">
            <a href="{{ route('post',$post->slug) }}" class="text-2xl font-bold text-gray-700 md:text-3xl dark:text-gray-200 hover:underline">
                {!! $post->title !!}
            </a>
            @if($post->description !== null)
                <p class="mt-2 text-gray-600 dark:text-gray-200">
                    {!! $post->description !!}
                </p>
            @endif
        </aside>
        <div class="flex items-center justify-end mt-4">
            <a href="{{ route('post',$post->slug) }}" class="text-primary-500 dark:text-primary-200 hover:underline">قراءة المزيد</a>

        </div>
    </div>
</article>
