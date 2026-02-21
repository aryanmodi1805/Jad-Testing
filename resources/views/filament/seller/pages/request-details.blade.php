<div class="h-full w-full relative">
    <div class="w-full h-full flex flex-col gap-6 items-start p-6 pt-10 max-md:!px-0 absolute">
        <header class="text-2xl font-bold">
            @lang('requests.requests')
        </header>

        @if(filled($this->requests))
            <div x-data="{details: window.innerWidth > 640 ? true:false }"
                 @resize.window="width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
                                if (width > 768) {
                                    details = true
                                    }else{
                                    details = false
                                    }"
                 class="flex gap-6 items-start w-full h-full relative overflow-hidden">
                <div class="relative h-full w-[50rem] flex flex-col gap-4 overflow-hidden ">
                    <div class="flex flex-col gap-4 h-full overflow-y-auto scrollbar-thin">
                        <div
                            class="h-fit flex w-full bg-white shadow-lg border sticky top-0 z-10 dark:bg-gray-900 dark:border-gray-800">
                            <div class="w-full h-16 p-4 flex items-center justify-between">
                                <p class="text-gray-600 dark:text-gray-400">@lang('string.showing') {{$this->requests->count()}} @lang('requests.requests_2')</p>

                                {{$this->filterAction()}}

                            </div>
                        </div>
                        <div class="flex flex-col gap-4 h-fit">
                            @foreach($this->requests as $request)
                                <div @click="details=true" wire:click="chooseRequest('{{ $request?->id }}')"
                                     class="flex flex-col gap-4 min-h-44 w-full bg-white border cursor-pointer
                                     p-4 hover:shadow-lg hover:bg-[#92298d05] relative overflow-hidden dark:bg-gray-900
                                     dark:border-gray-800 {{$request->id == $this->currentRequest->id ? 'shadow-lg before:bg-primary-600 before:absolute before:top-0 before:w-1 before:h-full before:start-0':''}}">
                                    <div class="flex gap-2 w-full justify-between">
                                        <div class="flex gap-2 items-center">
                                            <x-image
                                                :src="$request->customer->getFilamentAvatarUrl() ?? '/assets/profile-empty-state.webp'"
                                                class="bg-white h-12 w-12 border dark:border-gray-800"/>
                                            <div class="flex flex-col gap-1">
                                                <div class="font-bold">
                                                    {{$request->customer->name}}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{$request->service->name}}
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{$request->created_at->diffForHumans()}}
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap gap-2 w-full ">
                                        @if($request->is_invited)

                                            <div class="relative">
                                            <span class="absolute -mt-1 -ms-1 flex h-3 w-3">
                                                <span
                                                    class="w-full h-full absolute animate-ping inline-flex bg-primary-500 opacity-65 scale-90"></span>
                                                <span
                                                    class="w-full h-full inline-flex bg-primary-500"></span>
                                            </span>
                                                <x-filament::badge color="primary">
                                                    <div class="flex gap-1 items-center">
                                                        @lang('string.invited')
                                                    </div>
                                                </x-filament::badge>
                                            </div>

                                        @endif


                                        @if($request->customer->isPhoneVerified)
                                            <x-filament::badge color="success">
                                                <div class="flex gap-1 items-center">
                                                    <i class="ti ti-check"></i>
                                                    @lang('string.verified-phone')
                                                </div>
                                            </x-filament::badge>
                                        @endif
                                        @if($request->customer->requests_count > $regular_customer)
                                            <x-filament::badge color="secondary">
                                                <div class="flex gap-1 items-center">
                                                    <i class="ti ti-rotate-2"></i>
                                                    @lang('string.frequent-user')
                                                </div>
                                            </x-filament::badge>
                                        @endif
                                    </div>
                                    <div
                                        class="flex gap-2 items-start text-gray-600 dark:text-gray-400 dark:border-gray-800">
                                        <i class="ti ti-map-pin mt-1"></i>
                                        <p>{{ $request->location_name }}</p>
                                    </div>
                                    <div class="w-full flex justify-between mt-auto">
                                        <x-filament::badge size="lg" color="success">
                                            <div class="flex gap-1 items-center">
                                                <x-image src="/assets/logo/icon.svg" class="w-4 h-4"/>
                                                {{ $request->request_total_cost ?? 0 }} {{ __('wallet.credits') }}
                                            </div>
                                        </x-filament::badge>


                                        <div class="flex gap-2 items-center">
                                            <x-progress-bar :current="$request->responses_count?? 0"
                                                            :total="$this->maximum_responses" size="sm"/>
                                            @if(($request->responses_count ?? 0 )== 0)
                                                <p class="text-sm text-gray-800 dark:text-gray-200">@lang('string.1-st-to-respond')</p>
                                                @else
                                                    <p class="text-sm text-gray-800 dark:text-gray-200">{{($this->currentRequest->responses_count??0) >1?__('string.pros-responded.plural'):__('string.pros-responded.singular')}}
                                                    </p>
                                                @endif
                                        </div>
                                    </div>

                                </div>

                            @endforeach
                        </div>
                        <div class="w-full">
                            {{$this->requests->links()}}
                        </div>

                    </div>


                </div>
                <div wire:loading.flex wire:target="chooseRequest"
                     x-show="details"
                     class="w-full h-full inset-0 flex items-center justify-center bg-white dark:bg-gray-900 bg-opacity-75 max-md:absolute max-md:z-10">
                    <div class="flex h-full w-full border dark:border-gray-800 justify-center items-center">
                        <div role="status">
                            <svg aria-hidden="true"
                                 class="w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-primary-600"
                                 viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                    fill="currentColor"/>
                                <path
                                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                    fill="currentFill"/>
                            </svg>
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                @if(filled($this->currentRequest))
                    <div wire:loading.remove wire:target="chooseRequest"
                         x-show="details"
                         class="w-full h-full flex flex-col gap-4 relative overflow-y-auto scrollbar-thin max-md:absolute max-md:z-10 max-md:overflow-x-hidden max-md:bg-white max-md:dark:bg-gray-900 max-md:dark:border-gray-800">
                        <button
                            class="sticky top-3 left-[2%] mr-auto z-20 bg-white/80  border w-8 h-8 md:hidden dark:bg-gray-900/80 dark:border-gray-800"
                            @click.prevent="details=false">
                            <i class="ti ti-arrow-left text-lg"></i>
                        </button>
                        <div
                            class="bg-white p-6 border flex flex-col gap-4 top-0 z-10 max-md:relative dark:bg-gray-900 dark:border-gray-800">
                            <div class="flex gap-4 justify-between items-center w-full max-md:flex-col">
                                <div class="flex flex-col items-start ">
                                    <h4 class="text-xl font-bold">{{$this->currentRequest->service->name}}</h4>
                                </div>

                                <div class="flex gap-4 items-center">
                                    <x-filament::badge color="success">
                                        <div class="flex gap-1 items-center text-[1rem]">
                                            <x-image src="/assets/logo/icon.svg" class="w-6 h-6"/>
                                            {{ $this->currentRequest->request_total_cost ?? 0 }} {{ __('wallet.credits') }}
                                        </div>
                                    </x-filament::badge>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{$this->currentRequest->created_at->diffForHumans()}}
                                    </p>

                                </div>
                            </div>
                            <div
                                class="flex justify-between gap-4 items-center w-full bg-white border p-4 max-xl:flex-col dark:bg-gray-900 dark:border-gray-800">
                                <div class="flex gap-4">

                                    <x-image
                                        :src="$this->currentRequest->customer->getFilamentAvatarUrl() ?? '/assets/profile-empty-state.webp'"
                                        class="bg-white h-14 w-14 border dark:border-gray-800"/>
                                    <div class="flex flex-col gap-2 items-start">
                                        <div class="flex items-center gap-4">
                                            <h5 class=" font-bold">{{$this->currentRequest->customer->name}}</h5>
                                            <x-rate :rating="$this->currentRequest->customer->rate"
                                                    :total-reviews="$this->currentRequest->customer->rate_count"/>
                                        </div>

                                        <div class="flex flex-wrap gap-2 w-full ">

                                            @if($request->is_invited)

                                                <div class="relative">
                                                <span class="absolute -mt-1 -ms-1 flex h-3 w-3">
                                                    <span
                                                        class="w-full h-full absolute animate-ping inline-flex bg-primary-500 opacity-65 scale-90"></span>
                                                    <span
                                                        class="w-full h-full inline-flex bg-primary-500"></span>
                                                </span>
                                                    <x-filament::badge color="primary">
                                                        <div class="flex gap-1 items-center">
                                                            @lang('string.invited')
                                                        </div>
                                                    </x-filament::badge>
                                                </div>

                                            @endif
                                            @if($this->currentRequest->customer->isPhoneVerified)
                                                <x-filament::badge color="success">
                                                    <div class="flex gap-1 items-center">
                                                        <i class="ti ti-check"></i>
                                                        @lang('string.verified-phone')
                                                    </div>
                                                </x-filament::badge>
                                            @endif

                                            @if($this->currentRequest->customer->requests_count > $regular_customer)
                                                <x-filament::badge color="secondary">
                                                    <div class="flex gap-1 items-center">
                                                        <i class="ti ti-rotate-2"></i>
                                                        @lang('string.frequent-user')
                                                    </div>
                                                </x-filament::badge>
                                            @endif
                                        </div>
                                        <div
                                            class="flex gap-2 items-start text-gray-600 dark:text-gray-400 dark:border-gray-800">
                                            <i class="ti ti-map-pin mt-1"></i>
                                            <p>{{ $this->currentRequest->location_name }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-2 items-center w-fit border p-4 dark:border-gray-800 ">
                                    <x-progress-bar :current="$this->currentRequest->responses_count ?? 0"
                                                    :total="getMaxResponses()" size="sm"/>
                                    @if(($this->currentRequest->responses_count ?? 0) == 0)
                                        <p class="text-sm text-gray-800 dark:text-gray-200">@lang('string.1-st-to-respond')</p>

                                    @else
                                        <p class="text-sm text-gray-800 dark:text-gray-200">{{($this->currentRequest->responses_count??0) >1?__('string.pros-responded.plural'):__('string.pros-responded.singular')}}
                                        </p>
                                    @endif
                                </div>

                            </div>
                            <div class="w-full flex flex-wrap items-center justify-end gap-4  max-xl:justify-center ">
                                @if($this->currentRequest->is_invited)
                                    {{$this->cancelInvitationAction}}

                                @endif
                                {{$this->contactAction}}

                                {{$this->notInterested}}


                            </div>
                        </div>
                        <div class="flex flex-col gap-6 items-start  ">

                            <div
                                class="w-full grid grid-cols-4 max-md:grid-cols-1 gap-4 bg-white border p-6 dark:bg-gray-900 dark:border-gray-800">

                                <h5 class="text-lg font-bold col-span-3">@lang('string.details')</h5>
                                <div class="py-2 w-full col-span-4">
                                    <hr class="my-1 border-1 border-solid border-gray-200 sm:mx-auto dark:border-gray-700 "/>
                                </div>

                                <div class="col-span-3">
                                    <x-customer-answers :answers="$this->currentRequest->formattedAnswers()"/>
                                </div>

                                <div class="w-full flex flex-col items-start gap-4 col-span-3">
                                    <h5 class="font-bold text-lg">@lang('string.request_location')</h5>
                                    <hr class="w-full border-1 border-solid border-gray-200 sm:mx-auto dark:border-gray-700 "/>
                                </div>

                                <div class=" w-full h-[30rem] overflow-hidden col-span-4">
                                    <iframe
                                        width="100%"
                                        height="100%"
                                        style="border:0"
                                        loading="lazy"
                                        allowfullscreen
                                        referrerpolicy="no-referrer-when-downgrade"
                                        src="https://maps.google.com/maps?q={{$this->currentRequest->latitude}},{{$this->currentRequest->longitude}}&hl={{app()->getLocale()}}&z=16&amp;output=embed"
                                    >
                                    </iframe>
                                </div>
                            </div>

                        </div>
                    </div>
                @endif
            </div>
        @else

            <div
                class="h-full flex flex-col gap-4 justify-center items-center w-full bg-white border dark:bg-gray-900 dark:border-gray-800">

                <div class="p-4 bg-gray-200 dark:bg-gray-800">

                    <svg class="fi-ta-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" aria-hidden="true" data-slot="icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <p class="font-bold">@lang('requests.no_requests')</p>
                {{$this->filterAction()}}

            </div>
        @endif
    </div>
    <x-filament-actions::modals/>

</div>
