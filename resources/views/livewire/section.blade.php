<section class="{{$hidden ? 'hidden':''}} w-full">
    <div class="container mx-auto flex gap-8 items-center py-16 max-md:flex-col max-md:justify-center max-md:text-center max-md:p-6 {{$imagePosition === 'end' ? 'flex-row-reverse':''}}">
        <div class="w-full flex flex-col gap-4 p-8">
            <h3 class="text-4xl font-bold font-bold max-md:text-2xl">{!! $title!!}</h3>
            <p class="text-2xl font-medium max-md:text-lg">{!!html_entity_decode($content)!!}</p>
        </div>
        <div class="w-full p-8">
            <x-image src="{{$image}}"
                 alt="{{$title}}"/>
        </div>
    </div>
</section>
