<div>
    <div class="flex flex-col lg:flex-row lg:divide-x lg:divide-gray-200 bg-white dark:bg-gray-900">
        <!-- Sidebar -->
        <div class="bg-white dark:bg-gray-800 w-full lg:w-1/3 max-h-[700px] overflow-auto">
            <div class="p-4 text-white bg-gradient-to-r from-blue-500 to-blue-700">
                <div class="flex justify-between items-center">
                    <h1 class="font-bold text-xl">{{ __('labels.response_dashboard') }}</h1>
                    <button class="bg-blue-800 hover:bg-blue-900 text-sm py-1 px-2 rounded" wire:click="filterStatus(null)">
                        {{ __('labels.view_all') }}
                        <x-filament::loading-indicator wire:loading wire:target="filterStatus(null)" class="h-5 w-5" />
                    </button>
                </div>
            </div>
            <div class="p-2 text-sm flex justify-between items-center bg-blue-500 text-white">
                <span>{{ $responses->count() }} {{ __('labels.responses') }}</span>
                <button wire:click="send" class="bg-blue-700 px-2 py-1 rounded text-xs">
                    {{ __('labels.edit') }}
                    <x-filament::loading-indicator wire:loading wire:target="send" class="h-5 w-5" />
                </button>
            </div>
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 space-y-3">
                    <button class="w-full bg-blue-700 hover:bg-blue-800 text-white py-2 rounded-full focus:outline-none" wire:click="filterStatus('Pending')">
                        {{ __('labels.pending') }}
                        <x-filament::loading-indicator wire:loading wire:target="filterStatus('Pending')" class="h-5 w-5" />
                    </button>
                    <button class="w-full bg-blue-700 hover:bg-blue-800 text-white py-2 rounded-full focus:outline-none" wire:click="filterStatus('Hired')">
                        {{ __('labels.hired') }}
                        <x-filament::loading-indicator wire:loading wire:target="filterStatus('Hired')" class="h-5 w-5" />
                    </button>
                </div>
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($responses as $response)
                        <li
                            class="p-6 text-white dark:bg-gray-900 shadow-md hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer {{$selectedResponseId == $response->id ? ' border-r-4 border-blue-500' : ''}}"
                            wire:click="selectResponse('{{ $response->id }}')"
                        >
                            <div class="flex justify-between items-center mb-1">
                                <h3 class="font-semibold">{{ $response->request->customer->name }}</h3>
                                <x-filament::badge color="{{ $response->status->getColor() }}" icon="{{ $response->status->getIcon() }}">
                                    {{ $response->status->getLabel() }}
                                </x-filament::badge>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $response->request->service->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-300 mt-1">{{ $response->latestActivity?->details }} - {{ $response->latestActivity?->created_at->diffForHumans() }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <!-- Main Content -->
        <div class="lg:flex-grow p-8 bg-gray-100 dark:bg-gray-900 shadow-2xl overflow-auto max-h-[700px] rounded-3xl relative">
            <div wire:loading.flex wire:target="selectResponse" class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-800 bg-opacity-75">
                <x-filament::loading-indicator class="h-100 w-20 text-blue-500" />
            </div>
            <div wire:loading.remove wire:target="selectResponse">
                @if ($selectedResponse && $selectedResponse->status != \App\Enums\ResponseStatus::Invited)
                    <!-- Status Dropdown Container -->
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-lg mb-6 flex flex-col sm:flex-row items-center justify-between">
                        <div class="flex items-center space-x-4 rtl:space-x-reverse">
                            <span class="text-gray-600 dark:text-gray-300">{{ __('labels.current_status') }}:</span>
                            <x-filament::input.wrapper>
                                <x-filament::input.select disabled wire:model.defer="status">
                                    @foreach($this->getStatusOptions() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                        <div class="mt-2 sm:mt-0 text-xs text-gray-500 dark:text-gray-300">
                            {{ $selectedResponse->latestActivity?->details }} - {{ $selectedResponse->latestActivity?->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <!-- Response Details -->
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-lg mb-6">
                        <div class="flex items-center space-x-4 rtl:space-x-reverse">
                            <div class="w-16 h-16 rounded-full bg-gray-300 dark:bg-gray-700 flex items-center justify-center text-white text-2xl font-bold">
                                {{ substr($selectedResponse->request->customer->name, 0, 1) }}
                            </div>
                            <div>
                                <h2 class="font-bold text-2xl text-gray-800 dark:text-white">{{ $selectedResponse->request->customer->name }}</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $selectedResponse->request->customer->email }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $selectedResponse->location_name }}</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:space-x-4 rtl:space-x-reverse p-4">
                            {{$this->chatAction}}


                        </div>

                        <div class="space-y-2">
                            {{$this->estimateAction}}

                        </div>
                    </div>
                    <!-- Activities Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-lg mt-6 mb-6">
                        <h3 class="font-semibold text-2xl text-gray-800 dark:text-white mb-6">{{ __('labels.activities') }}</h3>
                        <ol class="relative border-s border-gray-200 dark:border-gray-700">
                            @foreach ($selectedResponse->activities as $activity)
                                <li class="mb-10 ms-6">
                                    @if ($activity->type == 'WhatsApp')
                                        <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900">
                                            <x-filament::icon icon="heroicon-m-chat-bubble-left-right" color="primary" class="w-5 h-5 text-blue-500 dark:text-blue-400" />
                                        </span>
                                    @elseif ($activity->type == 'Call')
                                        <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900">
                                            <x-filament::icon icon="heroicon-m-phone" color="primary" class="w-5 h-5 text-blue-500 dark:text-blue-400" />
                                        </span>
                                    @elseif ($activity->type == 'Status')
                                        <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900">
                                            <x-filament::icon icon="heroicon-m-pencil-square" color="primary" class="w-5 h-5 text-blue-500 dark:text-blue-400" />
                                        </span>
                                    @else
                                        <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900">
                                            <svg class="w-3.5 h-3.5 text-gray-800 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 0a12 12 0 100 24A12 12 0 1012 0zM12 22a10 10 0 110-20 10 10 0 010 20zM16.96 8.543l-4.608 4.598-1.82-1.818a1 1 0 00-1.414 0L6.344 12.298a1 1 0 101.415 1.414l1.792-1.79 1.792 1.791a1 1 0 001.415 0l4.828-4.815a1 1 0 000-1.414c-.393-.393-1.02-.393-1.414 0z"/>
                                            </svg>
                                        </span>
                                    @endif

                                    <h3 class="flex items-center mb-1 text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $activity->details }}

                                        @if ($loop->first)
                                            <span class="bg-blue-100 text-blue-800 text-sm font-medium ml-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
                                                {{ __('labels.latest') }}
                                            </span>
                                        @endif
                                    </h3>
                                    <time class="block mb-2 text-sm font-normal leading-none text-gray-400 dark:text-gray-500">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </time>
                                    <p class="mb-4 text-base font-normal text-gray-500 dark:text-gray-400">
                                        {{ $activity->status }}
                                    </p>
                                </li>
                            @endforeach
                        </ol>
                    </div>

                @elseif($selectedResponse && $selectedResponse->status == \App\Enums\ResponseStatus::Invited)
                    @if ($this->payRequestAction->isVisible())
                        {{ $this->payRequestAction }}
                    @elseif($this->selectedIsPurchased)
                        <!-- Display contact buttons -->
                        <button type="button" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-700 dark:hover:bg-blue-800 focus:outline-none dark:focus:ring-blue-800">
                            {{ __('labels.contact') }}
                        </button>
                    @else
                        <p class="text-lg text-gray-600 dark:text-gray-400">{{ __('labels.no_action_available') }}</p>
                    @endif
                @else
                    <p class="text-center text-gray-500 dark:text-gray-400">{{ __('labels.select_response') }}</p>

                @endif
            </div>
        </div>
    </div>
    <x-filament-actions::modals />

</div>
