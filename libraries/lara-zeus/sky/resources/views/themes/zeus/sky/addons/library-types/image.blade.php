<a href="{{ $file->getFullUrl() }}" target="_blank">
    <x-image alt="{{ $item->title }}-{{ $file->name }}" class="aspect-video mx-auto" src="{{ $file->getFullUrl() }}"/>
</a>
