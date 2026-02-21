<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Widgets\TreeCategoryWidget;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ManageRecords;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

}
