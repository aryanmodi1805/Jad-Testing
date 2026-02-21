<?php

namespace App\Filament\Seller\Resources;

use App\Filament\Seller\Resources\SellerLocationResource\RelationManagers\ServicesRelationManager;
use App\Models\SellerLocation;
use App\Models\SellerService;

use App\Rules\LatLngInCountry;
use App\Rules\OneNationwideLocation;
use App\Filament\Components\Geocomplete;
use App\Filament\Components\Map;
use Cheesegrits\FilamentGoogleMaps\Filters\RadiusFilter;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class SellerLocationResource extends Resource
{
    protected static ?string $model = SellerLocation::class;

    protected static ?string $slug = 'seller-locations';
    protected static bool $shouldSkipAuthorization = true;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 5 ;

    public static function getModelLabel(): string
    {
        return __('seller.locations.single');
    }

    public function getTitle(): string|Htmlable
    {
        return __('seller.locations.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('seller.locations.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->label(__('string.name')),

                TextInput::make('location_name')
                    ->required()
                    ->rule(fn($get) => new LatLngInCountry($get('location.lat'), $get('location.lng')))
                    ->label(__('string.wizard.descriptive_location'))
                    ->prefix(__('localize.Choose').':')
                    ->placeholder(__('services.requests.start_type')),

                Map::make("location")
                    ->label(__('string.wizard.map_location'))
                    ->columnSpanFull()
                    ->autocomplete(
                        fieldName: 'location_name',
                        placeField: 'name',
                        countries: [getCountryCode()]
                    )
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $set('latitude', $state['lat']);
                        $set('longitude', $state['lng']);
                    })
                    ->defaultLocation(getTenant()?->location ?? ['lat' => 24.7136, 'lng' => 46.6753])
                    ->geolocateOnLoad(fn(string $operation) => $operation === 'create', true)
                    ->geolocateLabel('')
                    ->draggable() // allow dragging to move marker
                    ->clickable(true) // allow clicking to move marker
                    ->geolocate() // adds a button to request device location and set map marker accordingly
                    ->autocompleteReverse(true)
                    ->defaultZoom(fn(string $operation) => $operation === 'create' ? 6 : 16)
                    ->geolocateOnLoad(true, false)
                    ->reactive()
                    ->geolocate(),



                Fieldset::make(__('string.default_service_location_settings'))
                    ->schema([
                        Toggle::make('is_nationwide')
                            ->default(0)
                            ->live()
                            ->inline(false)
                            ->label(__('columns.is_nationwide')),

                        Select::make('location_range')
                            ->options(__('string.distance_ranges'))
                            ->requiredIf('is_nationwide', false)
                            ->dehydratedWhenHidden()
                            ->dehydrateStateUsing(fn($component , $state) => $component->isHidden() ? null : $state)
                            ->hidden(fn($get) => $get('is_nationwide'))
                            ->label(__('columns.select_range')),


                    ]),

                Hidden::make('latitude')
                    ->required(),

                Hidden::make('longitude')
                    ->required(),

                Hidden::make('seller_id')
                    ->dehydrateStateUsing(fn() => auth('seller')->id()),


                Hidden::make('country_id')
                    ->dehydrateStateUsing(fn() => getCountryId()),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label(__('columns.name')),

                TextColumn::make('services_count')
                    ->label(__('columns.services')),

                TextColumn::make('services')
                    ->formatStateUsing(function ($state) {
                        return $state->service?->name;
                    })
                    ->badge()
                    ->label(__('services.services.plural')),

                ColumnGroup::make(__('string.default_service_location_settings'), [
                    IconColumn::make('is_nationwide')
                        ->boolean()
                        ->sortable()
                        ->label(__('columns.is_nationwide')),

                    TextColumn::make('location_range')
                        ->formatStateUsing(fn($state) => $state . ' ' . __('labels.kilometer'))
                        ->label(__('columns.range')),
                ])->alignCenter(),

            ])
            ->filters([


            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('addServices')
                    ->label(__('string.manage_services'))
                    ->action(function ($record, array $data) {
                        $record->services()->detach();
                        $record->services()->attach($data['sellerServices']);
                    })
                    ->form([
                        CheckboxList::make('sellerServices')
                            ->label(__('string.add_remove_service'))
                            ->options(function () {
                                return SellerService::where('seller_id', auth('seller')->id())
                                    ->with('service')
                                    ->whereHas('service')
                                    ->get()
                                    ->pluck('service.name', 'id');
                            })
                            ->default(function ($component, $record) {
                                return $record->services->pluck('id')->toArray();
                            })
                            ,
                    ])
                    ->modalSubmitActionLabel(__('string.save'))
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ServicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => SellerLocationResource\Pages\ListSellerLocations::route('/'),
            'create' => SellerLocationResource\Pages\CreateSellerLocation::route('/create'),
            'edit' => SellerLocationResource\Pages\EditSellerLocation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('seller', function (Builder $query) {
                $query->where('id', auth('seller')->id());
            })->with(['services.service'])->withCount(['services'=>function($query){
                $query->whereHas('service');
            }]);
    }
}
