<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use App\Models\City;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CityResource extends Resource
{

    protected static bool $isScopedToTenant = true;

    protected static ?string $model = City::class;

    protected static bool $shouldRegisterNavigation=false;

    protected static ?string $slug = 'cities';

    protected static ?int $navigationSort = -7 ;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getNavigationGroup(): ?string
    {
        return __('nav.regions');
    }
    public static function getNavigationLabel(): string
    {
        return __('regions.cities.plural');
    }

    public static function getModelLabel(): string
    {
        return __('regions.cities.single');
    }

    public function getTitle(): string|Htmlable
    {
        return __('regions.cities.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('regions.cities.plural');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),

                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->searchable(),

                Checkbox::make('active'),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('country.name')
                    ->searchable()
                    ->sortable(),

                ToggleColumn::make('active')
                    ->sortable(),
                ])
            ->filters([
                //
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['country']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'country.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];

        if ($record->country) {
            $details['Country'] = $record->country->name;
        }

        return $details;
    }
}
