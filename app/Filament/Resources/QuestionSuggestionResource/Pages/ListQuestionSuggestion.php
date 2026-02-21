<?php

namespace App\Filament\Resources\QuestionSuggestionResource\Pages;

use App\Filament\Resources\QuestionSuggestionResource;
use App\Traits\HasParentResource;
use Filament\Resources\Pages\ListRecords;

class ListQuestionSuggestion extends ListRecords
{
    use HasParentResource;
    protected static string $resource = QuestionSuggestionResource::class;

//    protected function getHeaderActions(): array
//    {
//        return [
//        ];
//    }
}
