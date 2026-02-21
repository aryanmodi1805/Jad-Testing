@props(['currentUser' => null, 'name' => '', 'isOtherPersonAgent' => false , 'other' => false])
@php
    use \App\Enums\MessageType;

    /* @var $message \App\Models\Message */
@endphp

<div class="flex w-full items-center h-10 gap-2 p-5">
    <x-filament::avatar
        src="https://ui-avatars.com/api/?name={{ urlencode($selectedConversation->seller?->company_name?? 'AB') }}"
        alt="Profile" size="lg" />
    <div class="flex flex-col">
        @php
            if ($currentUser->id === $selectedConversation->seller_id) {
                $isOtherPersonAgent = false;
            } else {
                $isOtherPersonAgent = true;
            }
        @endphp
        @if ($isOtherPersonAgent)
            <p class="text-base font-bold">{{ $selectedConversation->seller?->company_name }}</p>
        @else
            <p class="text-base font-bold">{{ $selectedConversation->request->customer?->name }}</p>
        @endif

        <p class="text-sm text-gray-500 dark:text-gray-400">
            @if($isOtherPersonAgent)
                @lang("accounts.sellers.single")
            @else
                @lang("accounts.customers.single")
            @endif
        </p>

    </div>
</div>

