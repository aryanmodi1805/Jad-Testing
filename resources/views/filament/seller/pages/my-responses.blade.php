@php use App\Enums\ResponseStatus; @endphp

<div class="h-full w-full relative">
    <div class="w-full h-full flex flex-col gap-6 items-start p-6 pt-10 max-md:!px-0 absolute">
        <header class="text-2xl font-bold">
            @lang('responses.my_responses')
        </header>

        @if(filled($this->responses))
            <div
                x-data="{details: window.innerWidth > 640}"
                @resize.window="details = window.innerWidth > 768"
                class="flex gap-6 items-start w-full h-full relative overflow-hidden"
            >

                {{-- LEFT LIST --}}
                <div class="relative h-full w-[50rem] flex flex-col gap-4 overflow-hidden">
                    <div class="flex flex-col gap-4 h-full overflow-y-auto scrollbar-thin">

                        <div class="h-fit flex w-full bg-white shadow-lg border sticky top-0 z-10 dark:bg-gray-900 dark:border-gray-800">
                            <div class="w-full h-16 p-4 flex items-center justify-between">
                                <p class="text-gray-600 dark:text-gray-400">
                                    @lang('string.showing') {{ $this->responses->count() }} @lang('responses.Responses')
                                </p>
                                {{ $this->filterAction() }}
                            </div>
                        </div>

                        <div class="flex flex-col gap-4">
                            @foreach($this->responses as $response)
                                <div
                                    wire:click="selectResponse('{{ $response->id }}')"
                                    @click="details=true"
                                    class="flex flex-col gap-4 min-h-44 w-full bg-white border cursor-pointer p-4 hover:shadow-lg relative overflow-hidden hover:bg-[#92298d05] dark:bg-gray-900 dark:border-gray-800
                                    {{ $response->id === optional($this->selectedResponse)->id ? 'shadow-lg before:bg-primary-600 before:absolute before:top-0 before:w-1 before:h-full before:start-0' : '' }}"
                                >

                                    <div class="flex gap-2 w-full justify-between">
                                        <div class="flex gap-2 items-center">
                                            <x-image
                                                :src="$response->request?->customer?->getFilamentAvatarUrl() ?? '/assets/profile-empty-state.webp'"
                                                class="bg-white h-12 w-12 border dark:border-gray-800"/>

                                            <div class="flex flex-col gap-1">
                                                <div class="font-bold">
                                                    {{ $response->request?->customer?->name ?? '—' }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $response->service?->name ?? '—' }}
                                                </div>
                                            </div>
                                        </div>

                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ optional($response->created_at)->diffForHumans() }}
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        @if($response->status === ResponseStatus::Invited)
                                            <x-filament::badge color="primary">@lang('string.invited')</x-filament::badge>
                                        @endif

                                        @if($response->request?->customer?->isPhoneVerified)
                                            <x-filament::badge color="success">@lang('string.verified-phone')</x-filament::badge>
                                        @endif

                                        @if(($response->request?->customer?->requests_count ?? 0) > $this->regular_customer)
                                            <x-filament::badge color="secondary">@lang('string.frequent-user')</x-filament::badge>
                                        @endif

                                        @if($response->status !== ResponseStatus::Invited)
                                            <x-filament::badge color="{{ $response->status?->getColor() }}">
                                                {{ $response->status?->getLabel() }}
                                            </x-filament::badge>
                                        @endif
                                    </div>

                                    <div class="w-full flex justify-between mt-auto">
                                        <x-filament::badge size="lg" color="success">
                                            {{ $response->request?->total_cost ?? 0 }} {{ __('wallet.credits') }}
                                        </x-filament::badge>

                                        <div class="flex gap-2 items-center">
                                            <x-progress-bar
                                                :current="$response->request_responses_count ?? 0"
                                                :total="$this->maximum_responses"
                                                size="sm"/>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{ $this->responses->links() }}
                    </div>
                </div>

                {{-- RIGHT DETAILS --}}
                @if(filled($this->selectedResponse))
                    <div
                        x-show="details"
                        class="w-full h-full flex flex-col gap-4 relative overflow-y-auto scrollbar-thin max-md:absolute max-md:z-10 max-md:bg-white dark:max-md:bg-gray-900"
                    >

                        <div class="bg-white p-6 border flex flex-col gap-4 dark:bg-gray-900 dark:border-gray-800">
                            <div class="flex justify-between items-center max-md:flex-col gap-4">
                                <h4 class="text-xl font-bold">
                                    {{ $this->selectedResponse?->service?->name ?? '—' }}
                                </h4>

                                <x-filament::badge color="success">
                                    {{ $this->selectedResponse?->request?->total_cost ?? 0 }} {{ __('wallet.credits') }}
                                </x-filament::badge>
                            </div>

                            <div class="flex gap-4 items-center">
                                <x-image
                                    :src="$this->selectedResponse?->request?->customer?->getFilamentAvatarUrl() ?? '/assets/profile-empty-state.webp'"
                                    class="h-14 w-14 border"/>

                                <div>
                                    <h5 class="font-bold">
                                        {{ $this->selectedResponse?->request?->customer?->name ?? '—' }}
                                    </h5>

                                    <x-rate
                                        :rating="$this->selectedResponse?->request?->customer?->rate ?? 0"
                                        :total-reviews="$this->selectedResponse?->request?->customer?->rate_count ?? 0"/>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @if($this->selectedResponse?->status === ResponseStatus::Invited)
                                    <x-filament::badge color="primary">@lang('string.invited')</x-filament::badge>
                                @endif

                                @if($this->selectedResponse?->request?->customer?->isPhoneVerified)
                                    <x-filament::badge color="success">@lang('string.verified-phone')</x-filament::badge>
                                @endif
                            </div>
                        </div>

                        <div class="bg-white border p-6 dark:bg-gray-900 dark:border-gray-800">
                            <h5 class="text-lg font-bold">@lang('responses.the_cost_estimate')</h5>
                            {{ $this->estimateInfolist }}
                        </div>

                        <div class="bg-white border p-6 dark:bg-gray-900 dark:border-gray-800">
                            <h5 class="text-lg font-bold">@lang('string.request_location')</h5>

                            @if(
                                filled($this->selectedResponse?->request?->latitude) &&
                                filled($this->selectedResponse?->request?->longitude)
                            )
                                <iframe
                                    class="w-full h-[30rem]"
                                    loading="lazy"
                                    src="https://maps.google.com/maps?q={{ $this->selectedResponse->request->latitude }},{{ $this->selectedResponse->request->longitude }}&hl={{ app()->getLocale() }}&z=16&output=embed">
                                </iframe>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="h-full flex flex-col gap-4 justify-center items-center w-full bg-white border dark:bg-gray-900 dark:border-gray-800">
                <p class="font-bold">@lang('responses.no_responses')</p>
                {{ $this->filterAction() }}
            </div>
        @endif
    </div>

    <x-filament-actions::modals/>
</div>
