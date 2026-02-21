<?php

namespace LaraZeus\Sky\Filament\Resources\FaqResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use LaraZeus\Sky\Filament\Resources\FaqResource;

class ListFaqs extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    public function getTitle(): string|Htmlable
    {
        return __('string.faq.title');

    }

    protected static string $resource = FaqResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
            LocaleSwitcher::make(),
        ];
    }
}
