<?php

namespace App\Livewire;

use App\Models\Service;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Support\Enums\IconSize;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class SearchWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'livewire.search-widget';

    public ?array $data = [
        'searchTerm' => null,
    ];

    public function mount()
    {
        $this->form->fill();

    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('searchTerm')
                    ->searchDebounce(100)
                    ->getSearchResultsUsing(fn(string $search, $get): array => Service::query()
                        ->where('id', $get('searchTerm'))
                        ->orWhere('name', 'like', "%{$search}%")
                        ->pluck('name', 'id')->toArray())
                    ->options(
                        Service::query()
                            ->where('id', $this->data['searchTerm'] ?? null)
                            ->orWhere('name', 'like', "%{$this->data['searchTerm']}%")
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->hiddenLabel()->required()
                    ->preload()
                    ->extraAttributes([
                        'class' => 'p-6 h-20 border-4 border-primary-500 flex items-center justify-center',
                    ])->live()
                    ->placeholder(__('labels.search_for_services'))
                    ->suffixAction(Actions\Action::make('search')
                        ->action(function ($livewire, $get) {
                            if ($get('searchTerm') != null) {
                                $livewire->dispatch('open-wizard', serviceId: $get('searchTerm'));
                            }
                        })
                        ->hiddenLabel()
                        ->iconSize(IconSize::Large)
                        ->iconButton()->extraAttributes(fn($get) => [
                            'style' => $get('searchTerm') != null && app()->getLocale() == 'ar' ? 'rotate: 180deg;' : ''
                        ])
                        ->icon(fn($get) => $get('searchTerm') == null ? 'heroicon-o-magnifying-glass' : 'heroicon-o-paper-airplane')),


            ])->columns(1)
            ->statePath('data');
    }

    public function openWizard($serviceId)
    {
        $this->dispatch('open-wizard', serviceId: (string)$serviceId);
    }


}
