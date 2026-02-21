@php
    use App\Enums\QuestionType;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Storage;

    /** @var \App\Models\Request|null $record */
    $record = $getRecord();

    /** @var Collection<int, array> $answers */
    $answers = collect($record?->formattedAnswers() ?? [])
        ->sortBy(fn(array $answer) => $answer['question_sort'] ?? 0)
        ->values();
@endphp

<div class="space-y-5">
    @forelse ($answers as $answer)
        @php
            $type = $answer['question_type'] ?? null;
            if (! $type instanceof QuestionType && filled($type)) {
                $type = QuestionType::tryFrom((int) $type);
            }

            $questionLabel = trim(($answer['question_sort'] ?? '') . ' ' . ($answer['question_label'] ?? ''));
            $badges = Arr::wrap($answer['answer_label'] ?? []);
        @endphp

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-colors dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {{ __('columns.question') }}
                    </p>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $questionLabel !== '' ? $questionLabel : __('labels.na') }}
                    </h4>
                </div>

                @if ($type instanceof QuestionType)
                    <x-filament::badge :color="$type->getColor() ?? 'primary'" class="inline-flex items-center gap-1">
                        <x-filament::icon :icon="$type->getIcon()" class="h-4 w-4" />
                        {{ $type->getLabel() }}
                    </x-filament::badge>
                @endif
            </div>

            <div class="mt-4 space-y-4 text-sm text-gray-700 dark:text-gray-200">
                @if (! empty($badges) && filled($badges[0]))
                    <div class="flex flex-wrap gap-2">
                        @foreach ($badges as $badge)
                            <x-filament::badge color="primary">
                                {{ $badge }}
                            </x-filament::badge>
                        @endforeach
                    </div>
                @endif

                @if (filled($answer['text_answer'] ?? null))
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        {{ $answer['text_answer'] }}
                    </div>
                @endif

                @php
                    $attachments = collect($answer['attachments'] ?? [])->filter();
                @endphp

                @if ($attachments->isNotEmpty())
                    <div class="space-y-2">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('labels.attachments') }}
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($attachments as $index => $attachment)
                                @php
                                    $path = is_array($attachment) ? ($attachment['path'] ?? null) : $attachment;
                                    $name = is_array($attachment) ? ($attachment['name'] ?? null) : null;

                                    if (filled($path) && ! filter_var($path, FILTER_VALIDATE_URL)) {
                                        $url = Storage::disk('public')->url($path);
                                    } else {
                                        $url = $path;
                                    }

                                    $displayName = $name
                                        ?? (is_array($attachment) ? ($attachment['label'] ?? null) : null)
                                        ?? __('string.attachment_no', ['number' => $loop->iteration]);
                                @endphp

                                @if (filled($url))
                                    <a
                                        href="{{ $url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 rounded-lg border border-primary-200 px-3 py-1.5 text-sm font-medium text-primary-700 transition-colors hover:bg-primary-50 dark:border-primary-500/40 dark:text-primary-200 dark:hover:bg-primary-500/10"
                                    >
                                        <x-filament::icon icon="heroicon-o-paper-clip" class="h-4 w-4" />
                                        {{ $displayName }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (filled($answer['voice_note'] ?? null))
                    <div class="space-y-2">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('labels.voice_note') }}
                        </p>
                        <audio controls class="w-full max-w-md">
                            <source src="{{ Storage::disk('public')->url($answer['voice_note']) }}" type="audio/mpeg" />
                        </audio>
                    </div>
                @endif

                @if (isset($answer['location']['lat'], $answer['location']['lng']))
                    <div class="space-y-3">
                        @if (filled($answer['location_name'] ?? null))
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                <span class="font-medium">{{ __('string.wizard.descriptive_location') }}:</span>
                                {{ $answer['location_name'] }}
                            </p>
                        @endif

                        <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm dark:border-gray-800">
                            <iframe
                                width="100%"
                                height="320"
                                style="border:0"
                                loading="lazy"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade"
                                src="https://maps.google.com/maps?q={{ $answer['location']['lat'] }},{{ $answer['location']['lng'] }}&hl={{ app()->getLocale() }}&z=16&output=embed"
                            ></iframe>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-6 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            {{ __('labels.na') }}
        </div>
    @endforelse
</div>
