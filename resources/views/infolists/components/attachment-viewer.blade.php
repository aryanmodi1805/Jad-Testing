<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div class="p-4 bg-white shadow rounded-lg">
        @foreach ($getAttachmentDetails() as $attachment)
            <div class="mb-4 border-b pb-4">
                {{-- Translate the details dynamically if needed --}}
                {{-- <div class="text-lg font-semibold mb-2">{{ $attachment['name'] }}</div> --}}
                {{-- <div class="text-sm text-gray-600 mb-2">@lang('string.type'): {{ $attachment['mime'] }}</div> --}}
                {{-- <div class="text-sm text-gray-600 mb-2"> --}}
                {{-- @lang('string.size'): {{ $attachment['size'] ? number_format($attachment['size'] / 1024, 2) . ' KB' : 'N/A' }} --}}
                {{-- </div> --}}

                @if (str_contains($attachment['mime'], 'image'))
                    <x-image src="{{ $attachment['url'] }}" alt="@lang('string.attachment_image')" class="max-w-full h-auto rounded-lg shadow-lg"/>
                @elseif ($attachment['mime'] == 'application/pdf')
                    <a href="{{ $attachment['url'] }}" target="_blank" class="text-blue-500 hover:text-blue-700 font-semibold">
                        <i class="far fa-file-pdf"></i> @lang('string.view_pdf')
                    </a>
                @elseif (in_array($attachment['mime'], ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']))
                    <a href="{{ $attachment['url'] }}" target="_blank" class="text-blue-500 hover:text-blue-700 font-semibold">
                        <i class="far fa-file-word"></i> @lang('string.view_word_document')
                    </a>
                @elseif (in_array($attachment['mime'], ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation']))
                    <a href="{{ $attachment['url'] }}" target="_blank" class="text-blue-500 hover:text-blue-700 font-semibold">
                        <i class="far fa-file-powerpoint"></i> @lang('string.view_powerpoint')
                    </a>
                @elseif ($attachment['mime'] == 'text/plain')
                    <a href="{{ $attachment['url'] }}" target="_blank" class="text-blue-500 hover:text-blue-700 font-semibold">
                        <i class="far fa-file-alt"></i> @lang('string.view_text_document')
                    </a>
                @else
                    <a href="{{ $attachment['url'] }}" target="_blank" class="text-blue-500 hover:text-blue-700 font-semibold">
                        <i class="far fa-file"></i> @lang('string.view_attachment')
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</x-dynamic-component>
