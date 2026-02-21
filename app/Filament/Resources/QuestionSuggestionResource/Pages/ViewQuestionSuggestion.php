<?php

namespace App\Filament\Resources\QuestionSuggestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Filament\Resources\QuestionSuggestionResource;
use App\Models\QuestionSuggestion;
use App\Traits\HasParentResource;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewQuestionSuggestion extends ViewRecord
{

    protected static string $resource = QuestionSuggestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
            ->before(fn ($record) => $record->answerSuggestions()->delete(),)
            ->after(fn() => $this->redirect(route('filament.admin.resources.services.index',[
                'tenant' => getCurrentTenant()
            ]))),

        ];
    }



}
