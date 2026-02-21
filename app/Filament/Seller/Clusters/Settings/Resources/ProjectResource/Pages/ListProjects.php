<?php

namespace App\Filament\Seller\Clusters\Settings\Resources\ProjectResource\Pages;

use App\Filament\Actions\PageSellerProfileAction;
use App\Filament\Seller\Clusters\Settings\Resources\ProjectResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            PageSellerProfileAction::make()->record(Filament::auth()->user()),

        ];
    }

}
