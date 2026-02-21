<?php

namespace App\Filament\Resources\QuestionResource\RelationManagers;

use App\Enums\QuestionType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionSuggestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questionSuggestions';

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextEntry::make('question.label')
                                    ->label(__('columns.question')),
                                TextEntry::make('name')
                                    ->label(__('columns.suggested_question_text')),
                            ])->columns(2),
                    ]),


                Grid::make()
                    ->schema([
                        TextEntry::make('seller.name')
                            ->label(__('columns.seller_name')),
                        TextEntry::make('type')
                            ->label(__('columns.type')),

                        TextEntry::make('question_type')
                            ->label(__('columns.question_type')),
                        TextEntry::make('created_at')
                            ->label(__('columns.created_date'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('columns.updated_date'))
                            ->dateTime(),
                    ])->columns(3)
            ]);
    }



    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('question.label')
                    ->label(__('columns.question'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('seller.name')
                    ->label(__('columns.seller_name'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn($record) => $record->type == 'create'? __('labels.create'):($record->type == 'edit'? __('labels.edit'):($record->type == 'delete'? __('labels.delete'):'')))
                    ->label(__('columns.type')),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('columns.suggested_question_text'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('question_type')
                    ->label(__('columns.question_type'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('columns.created_date'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('columns.updated_date'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(fn ($record) => $record->answerSuggestions()->delete(),),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(fn ($record) => $record->answerSuggestions()->delete(),),
                ]),
            ]);
    }
}
