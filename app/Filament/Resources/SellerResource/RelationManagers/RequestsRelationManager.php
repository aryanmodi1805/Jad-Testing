<?php

namespace App\Filament\Resources\SellerResource\RelationManagers;

use App\Models\Request;
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

class RequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'requests';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->required(),

                Select::make('service_id')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->required(),

                TextInput::make('status')
                    ->required(),

                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn(?Request $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn(?Request $record): string => $record?->updated_at?->diffForHumans() ?? '-'),

                TextInput::make('location_name'),

                TextInput::make('latitude')
                    ->numeric(),

                TextInput::make('longitude')
                    ->numeric(),

                TextInput::make('location_type')
                    ->required(),

                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->searchable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status'),

                TextColumn::make('location_name'),

                TextColumn::make('latitude'),

                TextColumn::make('longitude'),

                TextColumn::make('location_type'),

                TextColumn::make('country.name')
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
