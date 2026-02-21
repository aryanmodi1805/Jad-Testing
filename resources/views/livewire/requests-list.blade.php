<div>
    <div class="flex flex-col lg:flex-row lg:divide-x lg:divide-gray-200 bg-white dark:bg-gray-900">
        <div class="bg-white dark:bg-gray-800 w-full lg:w-1/3 max-h-[700px] overflow-auto">
            <div class="p-4 text-white bg-gradient-to-r from-blue-500 to-blue-700">
                <div>{{$requests->count()}} {{ __('labels.matching_leads') }}</div>
                <button @click="$dispatch('open-modal', { id: 'filter-modal' })"
                        class="bg-white text-blue-500 px-2 py-1 rounded text-xs">
                    {{ __('labels.filter') }}
                </button>
            </div>
            <div class="p-2 text-sm flex justify-between items-center bg-blue-500 text-white">
                <span>6 {{ __('labels.services') }} • 3 {{ __('labels.locations') }}</span>
                <button class="bg-blue-700 px-2 py-1 rounded text-xs">{{ __('labels.edit') }}</button>
            </div>
            <div class="p-4 dark:bg-gray-900">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white dark:bg-gray-900">{{ __('labels.dashboard') }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('labels.showing_all_leads', ['count' => $requests->count()]) }}</p>
            </div>
            <!-- Scrollable Container -->
            <div class="bg-white rounded-lg shadow">
                <!-- List of Leads -->
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($requests as $showRequest)
                        <li class="p-6 bg-white dark:bg-gray-900 shadow-md hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer {{$currentRequestId === $showRequest->id ? ' border-r-4 border-blue-500' : ''}}" wire:click="chooseRequest('{{ $showRequest->id }}')">
                            <div class="flex justify-between">
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white">{{ $showRequest->customer->name }} - {{ $showRequest->service->name }}</h3>
                                    <div class="flex items-center space-x-2 mt-2">
                                        <x-filament::badge size="lg" color="success">{{ $showRequest->total_cost }} {{ __('wallet.credits') }}</x-filament::badge>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $showRequest->customer->location }}</p>
                                </div>
                                <div class="rtl:text-left ltr:text-right">
                                    <span class="text-sm font-semibold text-blue-500">{{ $showRequest->created_at->diffForHumans() }}</span>
                                    <x-filament::badge  color="{{ $showRequest->status->getColor() }}" icon="{{ $showRequest->status->getIcon() }}">
                                        {{ $showRequest->status->getLabel() }}
                                    </x-filament::badge>
                                    @if(!$showRequest->responses->count())
                                        <div>
                                            <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                                                {{ __('labels.be_first_to_respond') }}
                                            </button>
                                        </div>
                                    @else
                                        <x-progress-bar :current="$showRequest->responses->count()" total="{{$maximum_responses}}"/>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Customer Details and Answers Section -->
        <div class="lg:flex-grow p-8 bg-gray-100 dark:bg-gray-900 shadow-2xl overflow-auto max-h-[700px] rounded-3xl relative">
            <div wire:loading.flex wire:target="chooseRequest" class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-900 bg-opacity-75 rounded-3xl">
                <x-filament::loading-indicator class="h-20 w-20 text-blue-500" />
            </div>
            <div wire:loading.remove wire:target="chooseRequest">
                @if ($request)
                    <!-- Customer Details Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-lg mb-6">
                        <div class="flex items-center mb-6">
                            <x-filament::icon icon="heroicon-m-user" color="primary" class="w-8 h-8 text-blue-500 dark:text-blue-400" />
                            <div class="ml-4">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('labels.customer_details') }}</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('labels.customer_information') }}</p>
                            </div>
                        </div>
                        <dl class="space-y-6">
                            <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm">
                                <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('columns.name') }}</dt>
                                <dd class="mt-1 text-lg text-gray-900 dark:text-white">{{ $customer->name ?? '-' }}</dd>
                            </div>
                            <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm flex items-center space-x-2 rtl:space-x-reverse">
                                <x-filament::icon icon="heroicon-m-envelope" color="primary" class="w-5 h-5 text-blue-500 dark:text-blue-400" />
                                <span class="text-lg text-gray-900 dark:text-white">
                                {{ ($this->is_purchased ? $customer->email : '*************' ) ?? '-' }}
                            </span>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-lg mb-6">
                        <!-- Total Cost Badge -->
                        <div class="flex justify-center lg:justify-start mt-4">
                            <x-filament::badge size="xl" color="success">
                                {{ $request ? $request->total_cost : 0 }} {{ __('wallet.credits') }}
                            </x-filament::badge>
                        </div>

                        <!-- Responses Section -->
                        <div class="mt-6">
                            <x-filament::fieldset>
                                <x-slot name="label">
                                    {{ __('labels.professionals_have_responded') }}
                                </x-slot>
                                <x-progress-bar :current="$request ? $request->responses->count() : 0" total="{{$maximum_responses}}"/>
                            </x-filament::fieldset>
                        </div>
                        <div class="text-center mt-6">
                            {{  auth(filament()->getAuthGuard())->user()->subscribedToService(service_id:$request->service->id)?'trtrtt':'xxxx'}}
                            @if ($this->payRequestAction->isVisible())
                                {{ $this->payRequestAction }}
                            @elseif($this->is_purchased)
                                <!-- Display contact buttons -->
                                {{$this->chatAction}}
                                <button type="button" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-700 dark:hover:bg-blue-800 focus:outline-none dark:focus:ring-blue-800">
                                    {{ __('labels.contact') }}
                                </button>
                            @else
                                <p class="text-lg text-gray-600 dark:text-gray-400">{{ __('labels.no_action_available') }}</p>
                            @endif
                            <x-filament-actions::modals />
                        </div>
                    </div>

                    <!-- Answers Section -->
                    @if(!empty($answers))
                        <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-lg">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('labels.answers') }}</h3>
                            <ul class="space-y-6">
                                @foreach($answers as $answer)
                                    <li class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 shadow-sm">
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $answer['question'] }}</h4>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $answer['answers'] }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-lg font-medium text-gray-700 dark:text-gray-300">{{ __('labels.no_answers_found') }}</p>
                        </div>
                    @endif

                @else
                    <p class="text-center text-gray-500 dark:text-gray-400">{{ __('labels.select_request') }}</p>
                @endif
            </div>
        </div>
    </div>
    <x-filament::modal id="filter-modal" width="lg" slide-over>
        <x-slot name="heading">
            {{ __('labels.filters') }}
        </x-slot>
        <div class="flex">
            <div class="p-6">
                <h4 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">{{ __('labels.services') }}</h4>
                <div class="flex items-center mb-6">
                    <label>
                        <x-filament::input.checkbox wire:model.live="selectAllServices" wire:click="toggleAllServices" />
                        <span>
                            {{ __('labels.select_all') }}
                        </span>
                    </label>
                </div>
                @foreach($serviceOptions as $service)
                    @if($service != null)
                        <div class="flex items-center mb-2">
                            <label>
                                <x-filament::input.checkbox wire:model.live="selectedServices" value="{{ $service?->id }}" />
                                <span>
                                {{ $service->name }}
                            </span>
                            </label>
                        </div>

                    @endif

                @endforeach
            </div>

            <div class="p-6">
                <h4 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">{{ __('labels.locations') }}</h4>
                <div class="flex items-center mb-6">
                    <label>
                        <x-filament::input.checkbox wire:model.live="selectAllLocations" wire:click="toggleAllLocations" />
                        <span>
                            {{ __('labels.select_all') }}
                        </span>
                    </label>
                </div>
                @foreach($locationOptions as $location)
                    <div class="flex items-center mb-2">
                        <label>
                            <x-filament::input.checkbox wire:model.live="selectedLocations" value="{{ $location->id }}" />
                            <span>
                                {{ $location->name }}
                            </span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button @click="$dispatch('close-modal', { id: 'filter-modal' })">{{ __('labels.cancel') }}</x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>
