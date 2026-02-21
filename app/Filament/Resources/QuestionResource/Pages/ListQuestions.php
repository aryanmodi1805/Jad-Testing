<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Traits\HasParentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListQuestions extends ListRecords
{
    use HasParentResource;

    protected static string $resource = QuestionResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('services.questions.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->url(
                fn (): string => static::getParentResource()::getUrl('questions.create', [
                    'parent' => $this->parent,
                ])
            ),
            Actions\Action::make('question-suggestions')
                ->label(__('string.question-suggestions'))
                ->url(fn (): string => static::getParentResource()::getUrl('question-suggestions.index', [
                    'parent' => $this->parent,
                ],
                )),
        ];
    }

}



