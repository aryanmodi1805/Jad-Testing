<?php

namespace App\Filament\Resources;

use App\Filament\Components\Map;
use App\Filament\Resources\CountryResource\Pages;
use App\Filament\Resources\CountryResource\RelationManagers;
use App\Forms\Components\TranslatableGrid;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Scopes\ActiveScope;
use App\Rules\LatLngInCountry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class CountryResource extends Resource
{
    use Translatable;

    protected static ?string $model = Country::class;
    protected static bool $isScopedToTenant = false;

    protected static ?int $navigationSort = -7;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('nav.regions');
    }

    public static function getNavigationLabel(): string
    {
        return __('regions.countries.plural');
    }

    public static function getModelLabel(): string
    {
        return __('regions.countries.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('regions.countries.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TranslatableGrid::make()->textInput()
                    ->required()
                    ->label(__('columns.name')),

                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->label(__('columns.code')),
                Forms\Components\Select::make('currency_id')
                    ->label(__('wallet.currency'))
                    ->relationship('currency')
                    ->searchable()
                    ->preload()
                    ->options(Currency::pluck('name', 'id')),
                Forms\Components\TextInput::make('credit_price')
                    ->label(__('nav.credit_cost'))
                    ->mask(RawJs::make('$money($input)'))
                    ->required(),

                Forms\Components\TextInput::make('vat_percentage')
                    ->label(__('wallet.packages.vat_percentage'))
                    ->prefix('%')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->mask(RawJs::make('$money($input)'))
                    ->required(),

                Forms\Components\Toggle::make('active')
                    ->required()
                    ->label(__('columns.active')),

                Map::make('location')
                    ->label(__('string.default_location'))
                    ->columnSpanFull()
                    ->autocomplete(
                        fieldName: 'location_name',
                        placeField: 'name',
                        countries: fn($record) =>[$record->code]
                    )
                    ->draggable() // allow dragging to move marker
                    ->clickable() // allow clicking to move marker
                    ->autocompleteReverse(true)
                    ->geolocate() // adds a button to request device location and set map marker accordingly
                    ->geolocateLabel(__('string.current_location')) // overrides the default label for geolocate button
                    ->defaultZoom(6)
                    ->geolocateOnLoad(),

                Forms\Components\TextInput::make('location_name')
                    ->required()
                    ->rule(fn($get) => new LatLngInCountry($get('location.lat'), $get('location.lng')))
                    ->label(__('string.wizard.descriptive_location'))
                    ->prefix(__('localize.Choose') . ':')
                    ->placeholder(__('services.requests.start_type'))
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->formatStateUsing(fn($record) => $record->full_name)
                    ->searchable()
                    ->label(__('columns.name')),

                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->label(__('columns.code')),
                Tables\Columns\TextColumn::make('currency.code')
                    ->searchable()
                    ->label(__('wallet.currency')),
                Tables\Columns\TextColumn::make('currency.symbol')
                    ->searchable()
                    ->label(__('wallet.currency_symbol')),
                Tables\Columns\TextColumn::make('credit_price')
                    ->searchable()
                    ->label(__('wallet.credit_cost')),
                Tables\Columns\ToggleColumn::make('active')
                    ->sortable()
                    ->label(__('columns.active')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('columns.created_at')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('columns.updated_at')),
                Tables\Columns\TextInputColumn::make('sort')
                    ->sortable()
                    ->label(__('columns.sort')),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label(__('columns.active'))
                    ->default(true)
                    ->options([
                        true => __('columns.active'),
                        false => __('columns.inactive'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->defaultSort('sort', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCountries::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([ActiveScope::class]);
    }

    public function getTitle(): string|Htmlable
    {
        return __('regions.countries.single');
    }
}
