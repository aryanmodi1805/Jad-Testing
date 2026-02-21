<div x-data="{
        scrollToBottom() {
            this.$nextTick(() => {
                let chatContainer = this.$refs.chatContainer;
                chatContainer.scrollTop = chatContainer.scrollHeight;
            });
        }
    }"
     x-init="scrollToBottom"
     x-on:livewire:load="scrollToBottom"
     x-on:livewire:update="scrollToBottom"
     wire:poll.5s
     class="w-full max-w-4xl mx-auto my-8 bg-gradient-to-br from-indigo-50 to-purple-100 dark:from-gray-800 dark:to-gray-900 shadow-lg rounded-lg flex flex-col h-[70vh]">
    <header class="p-4 bg-gradient-to-r from-indigo-400 to-indigo-600 dark:from-gray-700 dark:to-gray-800 shadow-md rounded-t-lg">
        <h1 class="text-lg font-semibold text-white tracking-wide">Chat</h1>
    </header>
    <div x-ref="chatContainer" class="flex-grow overflow-y-auto p-4 bg-white dark:bg-gray-800 custom-scrollbar messages">
        @foreach($response->messages as $message)
            <div class="flex mb-3 {{ $message->sender_type === 'App\Models\Customer' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-xs px-4 py-2 rounded-lg shadow-md {{ $message->sender_type === 'App\Models\Customer' ? 'bg-indigo-200 text-indigo-800 dark:bg-indigo-500 dark:text-indigo-100' : 'bg-green-200 text-green-800 dark:bg-green-500 dark:text-green-100' }}">
                    <span class="block text-xs font-semibold">{{ $message->sender->name }}</span>
                    <span class="block mt-1 text-sm">{{ $message->message }}</span>
                </div>
            </div>
        @endforeach
    </div>
    @if(!$disableChat)
        <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 rounded-b-lg">
            <form wire:submit.prevent="sendMessage" @submit.prevent="scrollToBottom" class="flex items-center space-x-3">
                <input type="text" wire:model="messageText" class="flex-grow px-4 py-2 mx-2 border rounded-full focus:outline-none focus:ring focus:ring-indigo-300 dark:focus:ring-indigo-600 bg-gray-100 dark:bg-gray-700 placeholder-gray-500 dark:placeholder-gray-300 ltr:placeholder-left rtl:placeholder-right" placeholder="{{ __('Type a message...') }}">
                <button type="submit" class="p-2 bg-indigo-500 dark:bg-indigo-600 text-white rounded-full hover:bg-indigo-600 dark:hover:bg-indigo-700 focus:outline-none focus:ring focus:ring-indigo-300 dark:focus:ring-indigo-600">
                    <x-filament::icon icon="heroicon-m-paper-airplane" color="primary" class="w-8 h-8" />

                </button>
            </form>
        </div>
    @endif
</div>
