<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Forms\Components\Translatable as ComponentsTranslatable;
use App\Models\Country;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CountriesRelationManager extends RelationManager
{
    protected static string $relationship = 'countries';

    public static function getModelLabel(): string
    {
        return __('regions.countries.single');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('regions.countries.plural');
    }

    public static function getPluralLabel(): ?string
    {
        return __('regions.countries.plural');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('name.en')
                    ->required()
                    ->maxLength(255)
                    ->label(__('columns.name_en')),
                TextInput::make('name.ar')
                    ->required()
                    ->maxLength(255)
                    ->label(__('columns.name_ar')),
                TextInput::make('code')
                    ->label(__('columns.code'))
                    ->required(),

                Toggle::make('active')
                ->label(__('columns.active')),

            ]);

    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('columns.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                ->label(__('columns.code')),

                TextColumn::make('active')
                    ->label(__('columns.active')),
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
