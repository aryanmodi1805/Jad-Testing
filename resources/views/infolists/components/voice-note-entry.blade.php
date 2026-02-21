<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div>
        @if ($url = $getVoiceNoteUrl())
            <audio controls style="width: 300px;">
                <source src="{{ $url }}" type="audio/mpeg">
            </audio>
        @else
            <p>No voice note available</p>
        @endif
    </div>
</x-dynamic-component>
