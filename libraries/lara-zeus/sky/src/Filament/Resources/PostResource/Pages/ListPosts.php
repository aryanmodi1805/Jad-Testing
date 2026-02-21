<?php

namespace LaraZeus\Sky\Filament\Resources\PostResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LaraZeus\Sky\Filament\Resources\PostResource;
use LaraZeus\Sky\SkyPlugin;

class ListPosts extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    public function getTitle(): string|Htmlable
    {
        return __('string.post.title');

    }

    protected static string $resource = PostResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
            LocaleSwitcher::make(),
        ];
    }


}
