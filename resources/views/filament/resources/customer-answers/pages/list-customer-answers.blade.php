@php
    $responses = \App\Models\Request::find($parent->id)->responses;
@endphp

<x-filament-panels::page>
    {{ $this->table }}

    @if ($responses->isNotEmpty())
        <h2>{{ __('labels.chats') }}</h2>
        @foreach ($responses as $response)
            <div>
                <h3>{{ __('labels.name_of_seller', ['name' => $response->seller->name]) }}</h3>
            </div>
        @endforeach
    @endif
</x-filament-panels::page>
