<div x-data="{ open: false, config: {} }"
     x-on:show-modal.window="console.log($event.detail[0]); config = $event.detail[0]; open = true"
     x-show="open"
     class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4">

    <div class="bg-white p-4 rounded shadow-lg">
        <h2 x-text="`Update ${config.type} Status`" class="font-bold"></h2>
        <div class="space-y-2 mt-4">
            <template x-for="(status, index) in config.statuses" :key="index">
                <button class="w-full rounded py-2 text-white"
                        :class="`bg-${config.colors[index]}`"
                        @click="$wire.call('updateCommunicationStatus', config.type, status)">
                    <span x-text="status"></span>
                </button>
            </template>
        </div>
        <button @click="open = false" class="mt-4 w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
            Close
        </button>
    </div>
</div>
