@php
    use App\Enums\QuestionType;
    use Filament\Support\Enums\IconSize;
    use Illuminate\Support\Facades\Storage;

    $answers = $this->getRecord()->formattedAnswers() ?? [];
@endphp
<div class="px-10 text-lg">
    @foreach($answers as $answer)
        <div>

        <span class="font-semibold">
            {{$answer['question_sort']}} - {{$answer['question_label']}}
        </span>
        </div>
        <div class="mt-2 mb-8">
            @if(in_array($answer['question_type'], [QuestionType::Checkbox , QuestionType::SELECT]) && isset($answer['answer_label']))
                <div class="flex ">

                    @if(is_array($answer['answer_label']))
                        @foreach($answer['answer_label'] as $label)
                            <x-badge>
                                {{ $label }}
                            </x-badge>
                        @endforeach
                    @else
                        <x-badge>
                            {{ $answer['answer_label'] }}
                        </x-badge>
                    @endif
                        <div class="flex-grow"></div>
                </div>
            @elseif(in_array($answer['question_type'], [
                QuestionType::Date,
                QuestionType::Number,
                QuestionType::TextArea,
                QuestionType::Text,
                QuestionType::PreciseDate,
                QuestionType::DateRange]) && isset($answer['text_answer']))
                <div class="flex ">
                    <x-badge >
                        {{$answer['text_answer']}}
                    </x-badge>

                    <div class="flex-grow"></div>
                </div>
                @elseif($answer['question_type'] ==  QuestionType::Attachments && (isset($answer['attachments']) || isset($answer['voice_note'])))
                    <div class="">
                        @if(isset($answer['attachments']) && count($answer['attachments']) > 0)
                            <div class="underline mt-4 mb-1">
                                {{ __('labels.attachments') }}
                            </div>
                            <div class="flex ">
                                @foreach($answer['attachments'] as $key => $attachment)
                                    @if(is_array($attachment) && array_key_exists('path',$attachment))
                                        <x-badge :attachment="true">
                                            <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="">
                                               {{__('string.attachment_no', ['number' => $attachment['name'] ?? $key ])}}
                                            </a>
                                        </x-badge>

                                    @endif

                                @endforeach
                                <div class="flex-grow"></div>
                            </div>

                        @endif

                        @if(isset($answer['voice_note']) )
                            <div class="underline mt-4 mb-1">
                                {{ __('labels.voice_note') }}
                            </div>
                            <div>
                                <audio src="{{Storage::url($answer['voice_note'])}}" controls class="mt-2 "></audio>
                            </div>
                        @endif

                    </div>

                @elseif($answer['question_type'] ==  QuestionType::Location && isset($answer['location']))
                    <div class="">
                        @if(isset($answer['location_name']))
                            <div class="mt-4 mb-1">
                                {{ __('string.wizard.descriptive_location') . ': ' }} <span>{{$answer['location_name']}}</span>
                            </div>
                        @endif

                        <div class="underline mt-4 mb-1">
                            {{ __('string.wizard.map_location') }}
                        </div>
                        <div class="relative w-full h-96">
                            <div class="absolute w-full h-full">
                                <iframe
                                    width="100%"
                                    height="100%"
                                    style="border:0"
                                    loading="lazy"
                                    allowfullscreen
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://maps.google.com/maps?q={{$answer['location']['lat']}},{{$answer['location']['lng']}}&hl={{app()->getLocale()}}&z=16&amp;output=embed"
                                >
                                </iframe>
                            </div>
                        </div>

                    </div>

                @endif

        </div>

    @endforeach
</div>
