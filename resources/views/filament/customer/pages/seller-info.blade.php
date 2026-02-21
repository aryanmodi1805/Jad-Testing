<x-filament::page>
    @foreach ($this->infolist()->getComponents() as $component)
        {{ $component->render() }}
    @endforeach
</x-filament::page>
