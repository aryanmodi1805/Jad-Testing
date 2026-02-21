<?php

namespace App\Filament\Seller\Resources\QuestionResource\Pages;

use App\Filament\Seller\Resources\QuestionResource;
use App\Traits\HasParentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuestion extends EditRecord
{
    use HasParentResource;

    protected static string $resource = QuestionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? static::getParentResource()::getUrl('questions.index', [
            'parent' => $this->parent,
        ]);
    }

    protected function configureDeleteAction(Actions\DeleteAction $action): void
    {
        $resource = static::getResource();

        $action->authorize($resource::canDelete($this->getRecord()))
            ->successRedirectUrl(static::getParentResource()::getUrl('questions.index', [
                'parent' => $this->parent,
            ]));
    }

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
