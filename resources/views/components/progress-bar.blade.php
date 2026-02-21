@props(['current','total','text'=>'','size'=>''])
<div class="flex items-center {{ $size=='sm'?'gap-1':'gap-2' }} rtl:space-x-reverse">
    @for ($i = 0; $i < $total; $i++)
        <div
            class="{{$size=='lg'?'w-6 h-6':($size=='sm'?'w-3 h-3':'w-4 h-4')}} {{ $i < $current ? 'bg-primary-500' : 'bg-gray-300 dark:bg-gray-600' }} rounded-full"></div>
    @endfor
    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $current }}/{{ $total }} {{$text}}</span>
</div>
