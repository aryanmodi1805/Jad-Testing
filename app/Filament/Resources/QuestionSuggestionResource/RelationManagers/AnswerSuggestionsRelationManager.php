<?php

namespace App\Filament\Resources\QuestionSuggestionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnswerSuggestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'answerSuggestions';

    public static function getModelLabel(): string
    {
        return __('labels.answer-suggestions');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('labels.answer-suggestions');
    }

    public static function getPluralLabel(): ?string
    {
        return __('labels.answer-suggestions');
    }
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('value')
            ->columns([
                Tables\Columns\TextColumn::make('value')
                ->label(__('columns.value')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
