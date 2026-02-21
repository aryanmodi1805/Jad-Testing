<div class="bg-[#f9f9fa]">
    <section class="flex flex-col items-center justify-center">
        <livewire:blog.blog-hero/>
        <div class="container flex flex-col gap-4 border border-solid border-gray-300 mt-8  p-6 z-10 relative rounded-lg ">
            <h4 class="text-2xl text-start max-sm:text-3xl w-full">@lang('blogs.tags')</h4>
            <div class="grid grid-cols-8 max-sm:grid-cols-2 max-md:grid-cols-3 max-xl:grid-cols-5 gap-4 ">
                <button type="button"
                        wire:click="filterByTag(null)"
                        :class="[' border border-secondary-500  hover:bg-secondary-500 text-base font-medium px-5 py-2.5 text-center me-3 mb-3 ', '{{$searchTags == null  ? 'bg-secondary-500 text-white':'bg-transparent text-secondary-500 hover:text-white'}}']">@lang('string.all')</button>
                @foreach($tags as $tag)
                    <button type="button"
                            wire:click="filterByTag({{$tag->id}})"
                            :class="[' border border-secondary-500  hover:bg-primary-500 text-base font-medium px-5 py-2.5 text-center me-3 mb-3 ', '{{$searchTags == $tag->id ? 'bg-secondary-500 text-white':'bg-transparent text-secondary-500 hover:text-white'}}']">{{$tag->name}}</button>
                @endforeach
            </div>
        </div>
        <livewire:blog.blog-posts/>
    </section>

    <livewire:footer/>


</div>
