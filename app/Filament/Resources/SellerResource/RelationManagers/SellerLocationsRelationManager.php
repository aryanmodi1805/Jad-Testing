<?php

namespace App\Filament\Resources\SellerResource\RelationManagers;

use App\Models\SellerLocation;
use App\Models\SellerService;
use App\Models\Service;
use App\Rules\OneNationwideLocation;
use App\Filament\Components\Geocomplete;
use App\Filament\Components\Map;
use Cheesegrits\FilamentGoogleMaps\Filters\RadiusFilter;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class SellerLocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'sellerLocations';

    public static function getModelLabel(): string
    {
        return __('seller.locations.single');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('seller.locations.plural');
    }

    public static function getPluralLabel(): ?string
    {
        return __('seller.locations.plural');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Map::make('location')
                    ->columnSpanFull()
                    ->reactive()
                    ->autocomplete(
                        fieldName: 'name',
                        countries: [getCountryCode()],
                    )
                    ->autocompleteReverse(true)
                    ->reverseGeocode([
                        'city' => '%L',
                        'location_type' => '%c',
                    ])
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $set('latitude', $state['lat']);
                        $set('longitude', $state['lng']);
                    })
                    ->geolocate()
                    ->geolocateOnLoad(fn(string $operation) => $operation === 'create', true),

                Toggle::make('is_nationwide')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        if (!$state) {
                            $set('longitude', null);
                            $set('name', null);
                            $set('latitude', null);
                        }
                    })
                    ->rule(function (?Model $record) {
                        return new OneNationwideLocation($record->seller_id, fn() => $record?->id);
                    })
                    ->default(0)
                    ->label(__('columns.is_nationwide')),

                Geocomplete::make('name')
                    ->reactive()
                    ->updateLatLng()
                    ->hiddenLabel()
                    ->geolocate()
                    ->countries([getCountryCode()])
                    ->prefix(__('localize.Choose').':')
                    ->placeholder(__('services.requests.start_type'))
                    ->visible(fn($get) => !$get('is_nationwide')),

                Hidden::make('latitude')
                    ->live()
                    ->required(fn($get) => !$get('is_nationwide'))
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $set('location', [
                            'lat' => floatVal($state),
                            'lng' => floatVal($get('longitude')),
                        ]);
                    })
                    ->hidden(fn($get) => $get('is_nationwide')),

                Hidden::make('longitude')
                    ->live()
                    ->required(fn($get) => !$get('is_nationwide'))
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $set('location', [
                            'lat' => floatVal($get('latitude')),
                            'lng' => floatVal($state),
                        ]);
                    })
                    ->hidden(fn($get) => $get('is_nationwide')),

                Hidden::make('location_type')
                    ->requiredIf('is_nationwide', 0)
                    ->hidden(fn($get) => $get('is_nationwide')),

                Select::make('location_range')
                    ->options([
                        '1' => '1 ' . __('labels.kilometer'),
                        '2' => '2 ' . __('labels.kilometers'),
                        '5' => '5 ' . __('labels.kilometers'),
                        '10' => '10 ' . __('labels.kilometers'),
                        '20' => '20 ' . __('labels.kilometers'),
                        '30' => '30 ' . __('labels.kilometers'),
                        '50' => '50 ' . __('labels.kilometers'),
                    ])
                    ->requiredIf('is_nationwide', 0)
                    ->hidden(fn($get) => $get('is_nationwide'))
                    ->label(__('columns.select_range'))
                    ->required(),
            ]);

    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label(__('columns.name')),

                TextColumn::make('services')
                    ->label(__('columns.services'))
                    ->formatStateUsing(function ($state, $record) {
                        return $record->services->count();
                    })
                    ->sortable(false)
                    ->searchable(false),

                TextColumn::make('country.name')
                    ->label(__('columns.country')),

                IconColumn::make('is_nationwide')
                    ->boolean()
                    ->sortable()
                    ->label(__('columns.is_nationwide')),

                TextColumn::make('location_range')
                    ->label(__('columns.range')),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->filters([


            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('addServices')
                    ->label(__('seller.services.add_services'))
                    ->action(function ($record, array $data) {

                        $record->services()->attach($data['sellerServices'],[
                            'is_nationwide' => $record->is_nationwide ,
                            'location_range' => $record->location_range
                        ]);
                    })
                    ->form([
                        CheckboxList::make('sellerServices')
                            ->options(function ($record) {
                                return Service::whereIn('id',SellerService::where('seller_id',$record->seller_id)
                                    ->pluck( 'service_id'))->pluck('name','id');

                            })
                            ->default(function ($component, $record) {
                                return $record->services->pluck('id')->toArray();
                            })
                            ->label('Services'),
                    ])
                    ->modalSubmitActionLabel(__('string.submit'))
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
