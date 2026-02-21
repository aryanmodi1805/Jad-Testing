@php
    if (isset($getSeller)) {
        $seller = $getSeller();
    }
    if (isset($getGallery)) {
        $gallery = $getGallery();
    }
@endphp

<div x-data="profileContainer" class="h-screen overflow-hidden relative">
    <div id="scrollable" @scroll.throttle="onScroll"
         class=" h-full w-full flex flex-col gap-6 overflow-y-scroll scrollbar-thin">
        <div class="w-full flex flex-col items-center ">
            <button class="absolute top-4 start-[1.25%] z-20 bg-white/80  border w-6 h-6"
                    @click.prevent="close()">
                <i class="ti ti-x "></i>
            </button>
            <div class="h-[10rem] w-full">
                <x-image class="object-cover w-full h-full"
                         src="{{$seller->getCoverImageUrl() ?? '/assets/photos/hero.jpg'}}"/>
            </div>

            @unless($seller->getFilamentAvatarUrl() == null)
                <div class="w-36 w-36 -mt-28 aspect-square outline outline-4 outline-white  overflow-hidden">
                    <x-image class="object-cover w-full h-full" src="{{$seller->getFilamentAvatarUrl()}}"/>
                </div>
            @endunless
        </div>

        <div class="w-full flex flex-col items-center gap-2 ">
            <h2 class="text-xl text-center font-bold">{{filled($seller->company_name)? $seller->company_name :$seller->name}}</h2>
            <div class="flex gap-4 text-sm">
                <div class="text-gray-600 flex gap-2 items-center">
                    <i class="ti ti-calendar text-md"></i>
                    <p> {{__('seller.joined').' '.$seller->created_at->translatedFormat('d M y')}}</p>
                </div>
            </div>
            <x-rate :rating="$seller->rate" :total-reviews="$seller->rate_count"/>

        </div>

        <div class="sticky top-0 mx-auto max-w-2/3 border-b border-gray-200 dark:border-gray-700 z-10 ">
            <ul x-ref="nav"
                class="flex flex-wrap mt-4 bg-white overflow-hidden shadow-lg justify-center -mb-[4px] text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                <li class="me-2 ">
                    <a href="#profile"
                       class="inline-flex gap-2 items-baseline justify-center p-4 border-b-2 border-transparent  hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group active">

                        <i class="ti ti-user text-xl"></i>@lang('labels.profile')
                    </a>
                </li>
                @unless($seller->rate == null)
                    <li class="me-2">
                        <a href="#reviews"
                           class="inline-flex gap-2 items-baseline justify-center p-4 border-b-2 border-transparent  hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group">
                            <i class="ti ti-star text-xl"></i>@lang('labels.reviews')
                        </a>
                    </li>
                @endunless

                @unless($seller->sellerProfileServices->isEmpty())
                    <li class="me-2">
                        <a href="#services"
                           class="inline-flex gap-2 items-baseline justify-center p-4 border-b-2 border-transparent  hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group">

                            <i class="ti ti-heart-handshake text-xl"></i>@lang('labels.services')
                        </a>
                    </li>
                @endunless

                @unless($seller->projects->isEmpty())
                    <li class="me-2">
                        <a href="#projects"
                           class="inline-flex gap-2  items-baseline justify-center p-4 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group">

                            <i class="ti ti-components text-xl"></i>@lang('labels.projects')
                        </a>
                    </li>
                @endunless

            </ul>
        </div>

        <div x-ref="sections" class="flex flex-col gap-8 mb-[40vh]">
            <section id="profile"
                     class=" scroll-mt-24 flex flex-col gap-6 w-full max-w-[90%] mx-auto max-md:scroll-mt-32">
                @unless($seller->getBio() == null)
                    <div>
                        <h4 class="text-2xl font-bold">@lang('labels.about')</h4>
                        <p>
                            {{$seller->getBio()}}
                        </p>
                    </div>
                @endunless

                @if($seller->years_in_business > 0 || $seller->hiredProjectsCount() > 0)
                    <div class="flex flex-col gap-4 p-6 border border-solid">
                        <h5 class="text-lg font-bold">@lang('labels.overview')</h5>
                        <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1">
                            @if($seller->years_in_business > 0)
                                <div class="flex gap-2  items-center">
                                    <i class="ti ti-calendar text-lg"></i>
                                    <span>@lang('labels.years_experience')</span>
                                    {{($seller->years_in_business ?? 0 ).' '.($seller->years_in_business > 1 ? __('labels.year.plural') : __('labels.year.singular'))}}
                                </div>
                            @endif
                            @if($seller->hiredProjectsCount() > 0)
                                <div class="flex gap-2 items-center">
                                    <i class="ti ti-checkbox text-lg"></i>
                                    <span>@lang('labels.hired')</span>
                                    {{$seller->hiredProjectsCount()}}
                                </div>
                            @endif
                        </div>
                    </div>

                @endif
                <div class="grid grid-cols-2 grow gap-4 max-md:grid-cols-1">
                    <div
                        class=" only:col-span-2 w-full max-md:max-w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5">
                        <h5 class="text-lg font-bold text-gray-900 dark:text-white mb-2">@lang('labels.contact-details')</h5>
                        <address
                            class="relative bg-gray-50 dark:bg-gray-700 dark:border-gray-600 p-4 border border-gray-200 not-italic grid grid-cols-3 gap-2">
                            @unless($seller->phone == null)
                                <div class="col-span-1 text-[0.9rem] text-gray-500 dark:text-gray-400 ">
                                    <i class="ti ti-phone"></i> <span
                                        class="hidden sm:inline">@lang('labels.contact')</span>
                                </div>
                                <div class="col-span-2">
                                    <span dir="ltr" class="break-all">
                                       {{$seller->phone}}
                                    </span>
                                </div>
                            @endunless
                            @unless($seller->email == null)
                                <div class="col-span-1 text-[0.9rem] text-gray-500 dark:text-gray-400 ">
                                    <i class="ti ti-mail"></i> <span
                                        class="hidden sm:inline">@lang('labels.email')</span>
                                </div>
                                <div class="col-span-2">
                                    <span dir="ltr" class="break-all">
                                       {{$seller->email}}
                                    </span>
                                </div>
                            @endunless
                        </address>
                    </div>
                    @unless($seller->socialMedia->isEmpty())
                        <div
                            class=" w-full max-w-md max-md:max-w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5">
                            <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">@lang('labels.social-media')</h5>
                            <address
                                class="relative bg-gray-50 dark:bg-gray-700 dark:border-gray-600 p-4 border border-gray-200 not-italic grid grid-cols-3 gap-2">
                                @foreach($seller->socialMedia as $social)
                                    <div
                                        class="flex gap-2 items-center col-span-1 text-[0.9rem] text-gray-500 dark:text-gray-400 ">
                                        <i class="ti ti-brand-{{ strtolower($social->platform) }}"></i>
                                        <span>{{ $social->platform }}</span>
                                    </div>
                                    <div dir="ltr" class="col-span-2 rtl:text-end truncate ...">
                                        <a href="{{ $social->link }}" target="_blank"
                                           class="break-all hover:text-[#83257f]">
                                            {{ str_replace('https://', '', $social->link) }}
                                        </a>
                                    </div>
                                @endforeach
                            </address>
                        </div>
                    @endunless
                </div>

            </section>

            @unless($seller->rate == null)
                <section id="reviews"
                         class="scroll-mt-24 max-w-[90%] w-full mx-auto flex flex-col gap-4 max-md:scroll-mt-32">
                    <h4 class="text-2xl font-bold">@lang('labels.reviews')</h4>
                    <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1 w-full">
                        @foreach($seller->ratings as $rate)
                            @unless(empty($rate->review))
                                <div class="only:col-span-2 w-full flex flex-col items-start gap-4 p-4 border">
                                    <x-rate :rating="$rate->rating"/>
                                    <p>
                                        {{$rate->review}}
                                    </p>
                                </div>
                            @endunless
                        @endforeach
                    </div>

                </section>
            @endunless

            @if(filled($gallery))

                <section id="#gallery" class="max-w-[90%] w-full mx-auto overflow-hidden flex flex-col gap-4">
                    <div class="flex justify-between">
                        <h4 class="text-2xl font-bold">@lang('labels.gallery')</h4>
                        <div class="flex gap-2 rtl:flex-row-reverse">

                            <!-- Scroll buttons -->
                            <button @click.prevent="scrollGalleryLeft"
                                    class="scroll-button left-0 p-2 border hover:bg-[#83257f0f]">
                                <i class="ti ti-arrow-left"></i>
                            </button>
                            <button @click.prevent="scrollGalleryRight"
                                    class="scroll-button right-0 p-2 border hover:bg-[#83257f0f]">
                                <i class="ti ti-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <div x-ref="gallery" class="overflow-x-auto scrollbar-thin">
                        <div class="w-fit flex gap-4 items-center">
                            <div
                                x-ignore
                                ax-load
                                ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('lightbox') }}"
                                x-data="lightbox({
                                            data: @js($gallery),
                                            showItemsInContainer: true,
                                        })"
                                class="flex w-fit"

                            >

                                <div x-ref="container" class="w-fit flex gap-4 items-center ">

                                </div>


                            </div>
                        </div>
                    </div>
                </section>
            @endif

            @if(filled($seller->sellerProfileServices))
                <section class="scroll-mt-24 max-w-[90%] w-full mx-auto flex flex-col gap-4 max-md:scroll-mt-32"
                         id="services">
                    <h4 class="text-2xl font-bold">@lang('labels.services')</h4>

                    <div class="w-full flex flex-col gap-4 items-center">
                        @foreach($seller->sellerProfileServices as $service)

                            <div x-data="{showA:false}" class="bg-white p-4 w-full border border-x-0">
                                <div class="cursor-pointer flex justify-between items-center w-full focus:outline-none"
                                     @click="showA=!showA">
                                    <span class="text-lg">{{$service->service_title}}</span>
                                    <svg
                                        class="w-5 h-5 text-gray-500 {{$service->service_description == null ? 'hidden':''}}"
                                        fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                                <div x-show="showA" class="mt-4 text-lg text-gray-500">
                                    {!! $service->service_description !!}
                                </div>
                            </div>

                        @endforeach
                    </div>


                </section>
            @endif

            @unless($seller->projects->isEmpty())
                <section class="scroll-mt-24 max-w-[90%] w-full mx-auto max-md:scroll-mt-32" id="projects">
                    <div class="flex justify-between">
                        <h4 class="text-2xl font-bold">@lang('labels.projects')</h4>
                        <div class="flex gap-2 rtl:flex-row-reverse">

                            <!-- Scroll buttons -->
                            <button @click.prevent="scrollProjectsLeft"
                                    class=" scroll-button left-0 p-2 border hover:bg-[#83257f0f]">
                                <i class="ti ti-arrow-left"></i>
                            </button>
                            <button @click.prevent="scrollProjectsRight"
                                    class="scroll-button right-0 p-2 border hover:bg-[#83257f0f]">
                                <i class="ti ti-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    <div x-ref="projects" class="overflow-x-auto flex items-center gap-4 scrollbar-thin">
                        @foreach($seller->projects as $project)

                            @php
                                if (isset($getProjectGallery)) {
                                    $mediaItems = $getProjectGallery($project);
                                }
                            @endphp


                            <div
                                x-ignore
                                ax-load
                                ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('lightbox') }}"
                                x-data="lightbox({
                                            data: @js($mediaItems),
{{--                                            showItemsInContainer: true,--}}
                                        })"
                                class="flex w-fit"
                            >
                                <div x-ref="container" class="w-fit flex gap-4 items-center ">
                                    <div
                                        class="flex flex-col justify-start w-full h-full gap-2 transition-transform transform  cursor-pointer py-4 ">
                                        <div
                                            class=" !w-[16rem] !h-[14rem] relative overflow-hidden hover:shadow-lg">
                                            <div
                                                class="absolute px-4  w-full h-full bg-gradient-to-t from-gray-700 from-10% via-transparent via-30%  to-transparent ">
                                                <p class="absolute text-white text-lg bottom-5 left-5">{{$project->title}}</p>
                                            </div>
                                            <x-image class="object-cover w-full h-full"
                                                     src="{{$project->getFirstMediaUrl('projects.main')}}"
                                                     alt="project"/>
                                        </div>
                                    </div>

                                </div>


                            </div>

                        @endforeach
                    </div>
                </section>
            @endunless

            @if(filled($seller->qas))
                <section class="max-w-[90%] w-full mx-auto flex flex-col gap-4" id="qas">
                    <h4 class="text-2xl font-bold">@lang('labels.qas')</h4>
                    <div class="w-full flex flex-col gap-4 items-center">
                        @foreach($seller->qas as $qa)

                            <div x-data="{showA:false}" class="bg-white p-4  w-full border border-x-0">
                                <div class="cursor-pointer flex justify-between items-center w-full focus:outline-none"
                                     @click="showA=!showA">
                                    <span class="text-lg ">{{$qa->question}}</span>
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                                <div x-show="showA" class="mt-4 text-lg text-gray-500">
                                    {!! $qa->answer !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</div>


