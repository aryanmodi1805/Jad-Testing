<div class="flex items-center rtl:space-x-reverse space-x-2 p-4" x-data="{ showTooltip: false, copyToClipboard() { navigator.clipboard.writeText('{{ $number }}').then(() => { this.showTooltip = true; setTimeout(() => { this.showTooltip = false; }, 2000); }) } }">
    <x-heroicon-o-phone class="h-6 w-6 text-blue-600 rtl:transform rtl:-scale-x-100" />
    <a href="tel:{{ $number }}" class="text-lg font-semibold text-gray-900 hover:text-blue-600">{{ $number }}</a>
    <button @click="copyToClipboard" class="relative flex items-center p-2 bg-gray-100 rounded-full text-gray-700 hover:text-blue-600 focus:outline-none">
        <template x-if="!showTooltip">
            <x-heroicon-o-clipboard class="h-6 w-6" />
        </template>
        <template x-if="showTooltip">
            <x-heroicon-o-clipboard-document-check class="h-6 w-6 text-blue-600" />
        </template>
        <span x-show="showTooltip" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-90" class="absolute bottom-full left-1/2 transform -translate-x-1/2 p-2 bg-blue-600 text-white text-xs rounded shadow-lg">
            Copied!
        </span>
    </button>
</div>
