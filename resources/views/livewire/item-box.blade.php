@props(['selectedConversation', 'currentUser' , 'isAdmin' => false])
<!-- Right Section (Chat Conversation) -->
<div
    class="flex flex-col w-full h-full  overflow-hidden relative">
    @if ($selectedConversation)
        <!-- Chat Messages -->

                <div x-data="{ markAsRead: false }" x-init="
                    $watch('markAsRead', value => {
                        console.log('markAsRead', value);
                    });
                    Echo.channel('chat')
                        .listen('.App\\Events\\MessageReadEvent', e => {
                            if (e.conversationId == @js($selectedConversation->id)) {
                                markAsRead = true;
                            }
                        })
                        .listen('.App\\Events\\MessageReceiverIsAwayEvent', e => {
                            if (e.conversationId == @js($selectedConversation->id)) {
                                markAsRead = false;
                            }
                        });

                    " id="chatContainer"
                     x-on:send-message.window="markAsRead = false"
                     class="flex-grow basis-0 flex flex-col-reverse p-5 overflow-y-auto  ">
                    <!-- Message Item -->
                    @foreach ($conversationMessages as $index => $message)
                            @php
                                $nextMessage = $conversationMessages[$index + 1] ?? null;
                                $nextMessageDate = $nextMessage ? \Carbon\Carbon::parse($nextMessage->created_at)->setTimezone(config('app.timezone'))->format('Y-m-d') : null;
                                $currentMessageDate = \Carbon\Carbon::parse($message->created_at)->setTimezone(config('app.timezone'))->format('Y-m-d');

                                // Show date badge if the current message is the last one of the day
                                $showDateBadge = $currentMessageDate !== $nextMessageDate;
                            @endphp
                        <div wire:key="{{ $message->id }}" class="chatMessageContainer">
                            @if ($showDateBadge)
                                <div class="flex justify-center my-4">
                                    <x-filament::badge>
                                        {{ \Carbon\Carbon::parse($message->created_at)->setTimezone(config('app.timezone'))->translatedFormat('j F, Y') }}
                                    </x-filament::badge>
                                </div>
                            @endif

                            @if (( !$isAdmin && $message?->sender_id !== $currentUser->id) || ($isAdmin && $message?->sender_id !== $selectedConversation->seller_id))
                                @php
                                    $previousMessageDate = isset($conversationMessages[$index - 1]) ? \Carbon\Carbon::parse($conversationMessages[$index - 1]->created_at)->setTimezone(config('app.timezone'))->format('Y-m-d') : null;

                                    $currentMessageDate = \Carbon\Carbon::parse($message->created_at)->setTimezone(config('app.timezone'))->format('Y-m-d');

                                    $previousSenderId = $conversationMessages[$index - 1]->sender_id ?? null;

                                    // Show avatar if the current message is the first in a consecutive sequence or a new day
                                    $showAvatar = ($message->sender_id !== $previousSenderId || $currentMessageDate !== $previousMessageDate);
                                @endphp
                                    <x-chat.message-item :message="$message" :name="$selectedConversation->otherParty()->name" :showAvatar="$showAvatar" :other="true" />
                            @else
                                <div x-data="{ show:false}" wire:key="{{$index}}" class="ownerChat">
                                    <div @click ="show = !show" wire:key="{{ $message->id }}.click">
                                        <x-chat.message-item :message="$message" name="" :showAvatar="false" :other="false" />
                                    </div>


                                    <div x-show="show || (markAsRead && $el.closest('.chatMessageContainer').isEqualNode($el.closest('#chatContainer').firstElementChild))" wire:key="{{ $message->id }}.read">
                                        <p class="text-xs text-gray-600 dark:text-primary-200 text-end">
                                            @lang('string.chat.read_at')
                                            @php
                                                $lastReadAt = \Carbon\Carbon::parse($message->read_at)->setTimezone(config('app.timezone')) ;

                                                if ($lastReadAt->isToday()) {
                                                    $date = $lastReadAt->format('g:i A');
                                                } else {
                                                    $date = $lastReadAt->format('M d, Y g:i A');
                                                }

                                            @endphp
                                            {{ $date }}
                                        </p>
                                    </div>
                                </div>

                            @endif
                        </div>

                    @endforeach
                    <!-- Repeat Message Item for multiple messages -->
                    @if ($this->paginator->hasMorePages())
                        <div x-intersect="$wire.loadMoreMessages" class="h-4">
                            <div class="w-full mb-6 text-center text-gray-500">@lang('string.chat.load_more')</div>
                        </div>
                    @endif
                </div>

            <!-- Chat Input -->

        <div class="w-full flex p-4 border-t max-h-96 dark:border-gray-800/60 border-gray-200/90">
            <form wire:submit="sendMessage" class="flex items-end justify-between w-full gap-4">
                <div class="w-full max-h-96 overflow-y-auto">
                    {{ $this->form }}
                </div>
            </form>
            <div class="w-12 ms-2 justify-end flex flex-col" >
                {{$this->groupAction()}}

                <div class="mt-2">
                    {{ $this->sendAction}}
                </div>
            </div>
        </div>

    @else
        <div class="flex flex-col items-center justify-center h-full p-3">
            <div class="p-3 mb-4 bg-gray-100 rounded-full dark:bg-gray-500/20">
                <x-filament::icon icon="heroicon-m-x-mark" class="w-6 h-6 text-gray-500 dark:text-gray-400" />
            </div>
            <p class="text-base text-center text-gray-600 dark:text-gray-400">
                @lang('string.chat.no_selected')
            </p>
        </div>
    @endif
        <x-filament-actions::modals />

</div>
@script
<script>
    $wire.on('chat-box-scroll-to-bottom', () => {

        chatContainer = document.getElementById('chatContainer');
        chatContainer.scrollTo({
            top: chatContainer.scrollHeight,
            behavior: 'smooth',
        });

        setTimeout(() => {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }, 400);
    });
</script>
@endscript
