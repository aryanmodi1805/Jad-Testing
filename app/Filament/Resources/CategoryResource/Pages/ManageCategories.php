<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Widgets\TreeCategoryWidget;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Resources\Pages\ManageRecords\Concerns\Translatable;
use Illuminate\Support\Str;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function updated($name)
    {
        $this->dispatch('updateTreeCategoryWidget');

    }

    protected function getHeaderWidgets(): array
    {
        return [
            TreeCategoryWidget::class
        ];
    }
}
