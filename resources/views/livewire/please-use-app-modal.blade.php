<div>
    @if($show)
        <div
            class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="please-use-app-title"
        >
            {{-- Backdrop --}}
            <div
                class="fixed inset-0 bg-gray-900/70 dark:bg-gray-900/80"
                wire:click="close"
            ></div>

            {{-- Modal box (centered, large) --}}
            <div
                class="relative w-full max-w-md rounded-xl bg-white dark:bg-gray-800 shadow-2xl p-10 text-center"
                wire:click.stop
            >
                <h2 id="please-use-app-title" class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
                    {{ __('seller.please_use_app') }}
                </h2>
                <button
                    type="button"
                    wire:click="close"
                    class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-10 py-4 text-lg font-semibold text-white shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                >
                    {{ __('OK') }}
                </button>
            </div>
        </div>
    @endif
</div>
