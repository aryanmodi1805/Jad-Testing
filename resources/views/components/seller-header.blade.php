
@props(['name' => '', 'rating' => 0])
<div class="grid grid-cols-1 sm:grid-cols-3 w-full">
    <span>{{$name}}</span>
    <div class="flex-auto"></div>
    <x-rate :rating="$rating" class="mt-1 justify-self-start sm:justify-self-end" />
</div>
