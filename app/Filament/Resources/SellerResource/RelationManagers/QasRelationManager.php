<?php

namespace App\Filament\Resources\SellerResource\RelationManagers;

use App\Models\SellerQA;
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

class QasRelationManager extends RelationManager
{
    protected static string $relationship = 'qas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('seller_id')
                    ->relationship('seller', 'name')
                    ->searchable()
                    ->required(),

                TextInput::make('answer')
                    ->required(),

                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn(?SellerQA $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn(?SellerQA $record): string => $record?->updated_at?->diffForHumans() ?? '-'),

                Select::make('q_a_s_id')
                    ->relationship('qAS', 'name')
                    ->searchable()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('seller.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('answer'),

                TextColumn::make('qAS.name')
                    ->searchable()
                    ->sortable(),
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
