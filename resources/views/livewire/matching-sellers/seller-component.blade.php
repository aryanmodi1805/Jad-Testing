@props(['fast_response_badge' => 60])

@php
    $seller = $getRecord();
@endphp

<div class="flex flex-col items-center gap-6 h-full w-full ">
    @php
        $distance = $seller->distance;
        $distanceText = number_format($distance, 2) . ' ' . __('labels.km');
    @endphp
    <div class="flex-auto flex flex-col gap-4 border rounded-t-xl w-full h-full overflow-hidden">
        <div class="w-full flex flex-col items-center top-0 h-fit ">
            <div class="h-32 w-full">
                <x-image class="object-cover w-full h-full"
                         src="{{$seller->getCoverImageUrl() ?? '/assets/photos/hero.jpg'}}"/>
            </div>
            @unless($seller->getFilamentAvatarUrl() == null)
                <div
                    style="width: 8rem; height: 8rem;"
                    class="-mt-16 aspect-square outline outline-4 outline-white rounded-xl overflow-hidden bg-white">
                    <x-image class="object-cover w-full h-full" src="{{$seller->getFilamentAvatarUrl()}}"/>
                </div>
            @endunless

        </div>

        <div class="w-full h-full flex flex-col items-center gap-2 ">
            <div class="flex items-center gap-4 justify-center">
                <h2 class="text-xl text-center ltr:font-mtb rtl:font-noto rtl:font-bold">{{filled($seller->company_name)? $seller->company_name :$seller->name}}</h2>
                <x-rate  :rating="$seller->rate"
                        :totalReviews="$seller->rate_count"/>
            </div>
            <div class="flex gap-4 text-sm flex-wrap justify-center">
                <div class="text-gray-600 flex gap-2 items-center">
                    <i class="ti ti-calendar text-lg"></i>
                    <p> {{__('seller.joined').' '.$seller->created_at->translatedFormat('d M y')}}</p>
                </div>
                <div class="text-primary-600 flex gap-2 items-center">
                    <i class="ti ti-rosette text-lg"></i>
                    <p>@lang('string.premium')</p>
                </div>
                <div class="text-secondary-500 flex gap-2 items-center">
                    <i class="ti ti-circle-dashed-check text-lg"></i>
                    <p>@lang('string.verified')</p>
                </div>
                @if($seller->average_response > 0 && $seller->average_response < $fast_response_badge)
                    <div class="text-success-500 flex gap-2 items-center">
                        <i class="ti ti-clock text-lg"></i>
                        <p>@lang('labels.quick_to_respond')</p>
                    </div>
                @endif

                @if($seller->years_in_business > 0)
                    <div class="text-gray-600 flex gap-2 items-center">
                        <p>@lang('labels.years_experience')</p>
                        <p>{{$seller->years_in_business }}</p>
                    </div>
                @endif


            </div>
            <div class="text-gray-800 flex gap-2 items-center">
                <i class="ti ti-map-pin text-lg"></i>
                <span>{{ __('labels.distance_away', ['distance' => $distanceText]) }}</span>
            </div>

            <div class="p-8 pt-2 flex flex-col gap-2 w-full">
                <div class="py-2 w-full">
                    <hr class="my-1 border-1 border-solid border-gray-200 sm:mx-auto dark:border-gray-700 "/>
                </div>
                <h4 class="ltr:font-mtb rtl:font-noto rtl:font-bold">@lang('labels.services')</h4>
                <div class="flex flex-wrap gap-2 mb-4 w-full ">
                    @foreach($seller->services as $service)
                        <span
                            class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300 rounded-full">{{ $service->name }}</span>
                    @endforeach
                </div>
                @if(filled($seller->getBio()))
                    <h4 class="ltr:font-mtb rtl:font-noto rtl:font-bold">@lang('labels.about')</h4>
                    <p class="text-gray-700">{{\Illuminate\Support\Str::limit($seller->getBio(),100 , '...' )}}</p>
                @endif
                <div class="flex-grow"></div>

            </div>
        </div>
    </div>

</div>
