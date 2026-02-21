<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
    <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ $getRecord()->service->name }}</h2>
    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $getRecord()->status }}</p>
    <p class="text-xs text-gray-500 dark:text-gray-300">{{ $getRecord()->created_at->format('d M, Y') }}</p>
    <p class="text-xs text-gray-500 dark:text-gray-300">{{ $getRecord()->updated_at->format('d M, Y') }}</p>
</div>
