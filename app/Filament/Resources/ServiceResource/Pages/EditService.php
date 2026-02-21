<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\QuestionResource\Pages\EditQuestion;
use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\Action::make('manage-questions')->label(__('string.manage_questions'))
                ->color('success')
                ->icon('heroicon-m-academic-cap')
                ->url(
                    fn($record): string => route('filament.admin.resources.services.questions.index',[
                        'parent' => $record,
                        'tenant' => getCurrentTenant()
                    ])
                )
        ];
    }
}
