<?php

namespace App\Filament\Resources\SellerResource\RelationManagers;

use App\Models\QuestionSuggestion;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionSuggestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questionSuggestions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('service_id')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->required(),

                TextInput::make('question_id'),

                TextInput::make('type')
                    ->required(),

                TextInput::make('name'),

                TextInput::make('question_type'),

                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn(?QuestionSuggestion $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn(?QuestionSuggestion $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('service.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('question_id'),

                TextColumn::make('type'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('question_type'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
