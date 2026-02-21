<?php

namespace App\Filament\Seller\Resources\QuestionResource\Pages;

use App\Filament\Seller\Resources\QuestionResource;
use App\Traits\HasParentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuestions extends ListRecords
{
    use HasParentResource;

    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [

            ];
    }
}
