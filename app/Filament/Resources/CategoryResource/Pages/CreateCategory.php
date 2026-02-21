<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Widgets\TreeCategoryWidget;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

}
