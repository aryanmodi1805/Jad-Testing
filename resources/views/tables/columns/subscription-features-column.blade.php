@php
    $stat= $getState();
    $record = $getRecord();
    $type=$getType();
   $services = $record->items()->where('type', $type)->get();
@endphp
<div>
    @if($stat)
        <p class="text-sm mb-1 font-semibold text-gray-900 dark:text-white">{{ $stat->getLabel() ??""}}</p>

        <ul class=" text-sm text-left text-gray-500 dark:text-gray-400">

            @foreach( $services as $service)

                <li class="flex items-center space-x-3 rtl:space-x-reverse">
                    <svg class="flex-shrink-0 w-3 h-3 text-primary-600 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                    </svg>
                    <span> {{ $service->getName($stat) }} </span>
                </li>
            @endforeach


        </ul>
    @endif

</div>
