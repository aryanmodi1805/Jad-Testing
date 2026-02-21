@props(['message' => null, 'name' => '', 'showAvatar' => false , 'other' => false])
@php
    use \App\Enums\MessageType;

    /* @var $message \App\Models\Message */
@endphp

@if($message != null)
    <div @class([
        "flex items-end gap-2 mb-2",
        "flex-col" => !$other,
    ])>
        @if ($other && $showAvatar)
            <x-filament::avatar
                src="https://ui-avatars.com/api/?name={{ urlencode($name) }}"
                alt="Profile" size="sm" />
        @else
            <div class="w-6 h-6"></div>
        @endif

        <div @class([
                "max-w-md p-2 rounded-t-xl rounded-bl-xl  rounded-tl-xl rounded-br-xl text-start",
                "dark:bg-gray-800 bg-gray-200" => $other,
                "bg-primary-600 dark:bg-primary-500 text-white" => !$other,
            ])>

            @if($message->payloadType() == MessageType::Estimate)
                <div class="my-2 mx-6 ">
                    <p class="text-sm font-bold leading-6">@lang('string.chat.estimate_sent')</p>
                    <p @class([
                                "text-xs leading-8 font-bold",
                                "text-primary-500" => $other,
                                "text-secondary-500" => !$other,
                    ])>{{$message->estimatePrice()}} </p>

                    @if($message->hasDetails())
                        <p class="text-xs font-bold leading-6 mb-1">@lang('string.chat.estimate_details')</p>

                        <p @class(["text-xs",
                            "text-gray-600" => $other,
                            "text-gray-400" => !$other,
                        ])>
                            {{$message->estimateDetails() }}
                        </p>
                    @endif

                </div>


            @elseif($message->message)
                <p class="text-sm">{{ $message->message }}</p>
            @endif

            @if ($message->attachments && count($message->attachments) > 0)
                @foreach ($message->attachments as $attachment)
                    @php
                        $originalFileName = $this->getOriginalFileName($attachment, $message->original_attachment_file_names);
                    @endphp
                    <div wire:click="downloadFile('{{ $attachment }}', '{{ $originalFileName }}')"
                         @class([
                            "flex items-center gap-1 p-2 my-2 gap-1 rounded-lg group cursor-pointer",
                            "bg-gray-50 dark:bg-gray-700" => $other,
                            "bg-primary-500 dark:bg-primary-800" => !$other,
                        ])>
                        <div
                            @class([
                                "p-2 rounded-full ",
                                "bg-gray-500 dark:bg-gray-600 group-hover:bg-gray-700 group-hover:dark:bg-gray-800" => $other,
                                "text-white bg-primary-600 group-hover:bg-primary-700 group-hover:dark:bg-primary-900" => !$other,
                            ])>
                            @php
                                $icon = 'heroicon-m-x-mark';

                                if($this->validateImage($attachment)) {
                                    $icon = 'heroicon-m-photo';
                                }

                                if ($this->validateDocument($attachment)) {
                                    $icon = 'heroicon-m-paper-clip';
                                }

                                if ($this->validateVideo($attachment)) {
                                    $icon = 'heroicon-m-video-camera';
                                }

                                if ($this->validateAudio($attachment)) {
                                    $icon = 'heroicon-m-speaker-wave';
                                }

                            @endphp
                            <x-filament::icon icon="{{ $icon }}" class="w-4 h-4" />
                        </div>
                        <p @class([
                                "text-sm group-hover:underline",
                                "text-gray-600 dark:text-white" => $other,
                                "text-white" => !$other,
                        ])

                            >
                            {{ $originalFileName }}
                        </p>
                    </div>
                @endforeach
            @endif
            <p
                @class([
                    "mt-1 text-xs",
                    "text-gray-500 dark:text-gray-600 text-start" => $other,
                    "text-primary-300 dark:text-primary-200 text-end" => !$other,

                ])>
                @php
                    $createdAt = \Carbon\Carbon::parse($message->created_at)->setTimezone(config('app.timezone'));

                    if ($createdAt->isToday()) {
                        $date = $createdAt->format('g:i A');
                    } else {
                        $date = $createdAt->format('M d, Y g:i A');
                    }
                @endphp
                {{ $date }}
            </p>
        </div>
    </div>
@endif

