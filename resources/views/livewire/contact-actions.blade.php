<div class="w-full h-full flex flex-col gap-6 items-center">
    <h4 class="p-6 text-xl ltr:font-mtb rtl:font-noto rtl:font-bold">{{__('string.contact') . ' ' . $record->customer->name}}</h4>

    <div class="w-full p-6 flex gap-4 h-32 justify-center">
        {{ $action->getModalAction('call') }}
        {{ $action->getModalAction('email') }}
    </div>

<div class="w-full">
    <hr class="w-full border-1 border-solid border-gray-200 sm:mx-auto dark:border-gray-700 "/>
    <button @click.prevent="close()" class="border border-0 border-t-1 w-full p-4">
        @lang('string.close')
    </button>

</div>
</div>
