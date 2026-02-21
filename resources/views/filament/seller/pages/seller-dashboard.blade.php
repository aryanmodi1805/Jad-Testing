<x-filament-panels::page class="fi-dashboard-page">
    {{-- Big centered "Please use the app" modal (shown after redirect from restricted feature) --}}
    @if($showPleaseUseAppModal)
        <div
            class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-4 md:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="please-use-app-title"
        >
            <div
                class="fixed inset-0 bg-gray-900/70 dark:bg-gray-900/80"
                wire:click="closePleaseUseAppModal"
            ></div>
            <div
                class="relative w-full max-w-[90%] sm:max-w-md md:max-w-lg rounded-xl bg-white dark:bg-gray-800 shadow-2xl p-6 sm:p-8 md:p-10 text-center"
                wire:click.stop
            >
                <h2 id="please-use-app-title" class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6 sm:mb-8 px-2">
                    {{ __('seller.please_use_app') }}
                </h2>
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
                    <button
                        type="button"
                        wire:click="closePleaseUseAppModal"
                        class="inline-flex items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700 px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-semibold text-gray-900 dark:text-white shadow-sm hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors w-full sm:w-auto"
                    >
                        {{ __('seller.close') }}
                    </button>
                    <button
                        type="button"
                        onclick="openJadApp()"
                        class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-semibold text-white shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors w-full sm:w-auto"
                    >
                        {{ __('seller.open_app') }}
                    </button>
                </div>
            </div>
            
            <script>
                function openJadApp() {
                    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                    const isMac = /Macintosh|MacIntel|MacPPC|Mac68K/.test(navigator.userAgent);
                    const isAndroid = /Android/.test(navigator.userAgent);
                    
                    const appDeepLink = 'jadservices://seller';
                    const iosStoreUrl = 'https://apps.apple.com/sa/app/jad-services/id6751058593';
                    const androidStoreUrl = 'https://play.google.com/store/apps/details?id=services.jad.app';
                    
                    // Try to open the app first (deep link configured in app)
                    window.location.href = appDeepLink;
                    
                    // If app is not installed, redirect to store after a delay
                    setTimeout(function() {
                        if (isIOS || isMac) {
                            window.location.href = iosStoreUrl;
                        } else if (isAndroid) {
                            window.location.href = androidStoreUrl;
                        } else {
                            window.location.href = androidStoreUrl;
                        }
                    }, 800);
                }
            </script>
        </div>
    @endif

    @if (method_exists($this, 'filtersForm'))
        {{ $this->filtersForm }}
    @endif

    <x-filament-widgets::widgets
        :columns="$this->getColumns()"
        :data="
            [
                ...(property_exists($this, 'filters') ? ['filters' => $this->filters] : []),
                ...$this->getWidgetData(),
            ]
        "
        :widgets="$this->getVisibleWidgets()"
    />
</x-filament-panels::page>
