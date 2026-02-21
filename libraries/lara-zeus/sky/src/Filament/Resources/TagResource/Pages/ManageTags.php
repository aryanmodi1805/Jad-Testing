<?php

namespace LaraZeus\Sky\Filament\Resources\TagResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Contracts\Support\Htmlable;
use LaraZeus\Sky\Filament\Resources\TagResource;

class ManageTags extends ManageRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = TagResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('string.tag.title');

    }

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
            LocaleSwitcher::make(),
        ];
    }
}
