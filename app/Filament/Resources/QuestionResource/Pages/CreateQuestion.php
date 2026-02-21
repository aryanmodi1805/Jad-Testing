<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Traits\HasParentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateQuestion extends CreateRecord
{
    use HasParentResource;
    protected static string $resource = QuestionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? static::getParentResource()::getUrl('questions', [
            'parent' => $this->parent,
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data[$this->getParentRelationshipKey()] = $this->parent->id;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $question = parent::handleRecordCreation($data);

        if (isset($data['is_custom']) && $data['is_custom']) {

            $question->answers()->create([
                'val' => 0,
            ]);

        }
        return $question;
    }
}
