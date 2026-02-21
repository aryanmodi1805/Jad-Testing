<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageResource\Pages;
use App\Filament\Resources\PackageResource\RelationManagers;
use App\Forms\Components\Translatable;
use App\Models\Currency;
use App\Models\Package;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $isScopedToTenant = true;
    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string
    {
        return __('nav.wallet_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('wallet.packages.plural');
    }

    public static function getModelLabel(): string
    {
        return __('wallet.packages.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('wallet.packages.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Translatable::make(__('wallet.packages.name'))
                    ->name('name')
                    ->columns()
                    ->label(__('wallet.packages.name')),

                Translatable::make(__('wallet.packages.description'))
                    ->required()
                    ->name('description')
                    ->columns()
                    ->label(__('wallet.packages.description')),

                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('credits')->label(__('wallet.credits'))
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('price')->label(__('wallet.packages.price'))
                            ->required()
                            ->numeric()
                            ->prefix(fn() => Filament::getTenant()?->currency?->symbol ?? "*"),
                        Forms\Components\TextInput::make('price_with_vat')
                            ->label(__('wallet.packages.final_price'))
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($state, $set, $get) {
                                $vat = (Filament::getTenant()?->vat_percentage ?? 0) / 100;
                                $price = $get('price');
                                $exVAT = $get('ex_VAT');
                                $set('price_with_vat', $exVAT ? $price : $price + ($price * $vat));
                            }),
                    ]),


                Forms\Components\Toggle::make('ex_VAT')
                    ->label(__('wallet.packages.inc (VAT)', ['p' => Filament::getTenant()?->vat_percentage ?? 0]))
                    ->default(true)->required()->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $vat = ((Filament::getTenant())->vat_percentage ?? 0) / 100;
                        $price = $get('price');
                        $exVAT = $get('ex_VAT');
                        $set('price_with_vat', $exVAT ? $price : $price + ($price * $vat));
                    }),
                Forms\Components\Select::make('currency_id')->label(__('wallet.currency'))
                    ->required()->inlineLabel()
                    ->default(Filament::getTenant()?->currency?->id ?? "")
                    ->searchable()
                    ->options(Currency::get()->pluck('name', 'id')),

                // iOS In-App Purchase Fields
                Forms\Components\Section::make(__('wallet.packages.ios_settings'))
                    ->description(__('wallet.packages.ios_settings_description'))
                    ->schema([
                        Forms\Components\TextInput::make('apple_product_id')
                            ->label(__('wallet.packages.apple_product_id'))
                            ->placeholder('jad_100_credits')
                            ->helperText(__('wallet.packages.apple_product_id_help'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('ios_price')
                            ->label(__('wallet.packages.ios_price'))
                            ->numeric()
                            ->prefix(fn() => Filament::getTenant()?->currency?->symbol ?? "*"),

                        Forms\Components\Toggle::make('is_ios_active')
                            ->label(__('wallet.packages.ios_active'))
                            ->helperText(__('wallet.packages.ios_active_help'))
                            ->default(false),

                    ])
                    ->collapsible()
                    ->collapsed(),

//                Forms\Components\TextInput::make('discount')->label(__('wallet.packages.discount'))->prefix('%')
//                    ->maxLength(255)
//                    ->default(null),

                Forms\Components\Toggle::make('is_best_value')->label(__('wallet.packages.best_value'))
                    ->required(),
                Forms\Components\Toggle::make('is_active')->label(__('string.active'))
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('wallet.packages.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')->label(__('wallet.packages.description'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('credits')->label(__('wallet.credits'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')->label(__('wallet.packages.price'))->sortable(),
                Tables\Columns\TextColumn::make('price_with_vat')->label(__('wallet.packages.final_price'))->sortable(),

                Tables\Columns\TextColumn::make('currency.symbol')->label(__('wallet.currency'))
                    ->searchable(),

                // iOS Columns
                Tables\Columns\IconColumn::make('is_ios_active')->label(__('wallet.packages.ios_active'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('apple_product_id')->label(__('wallet.packages.apple_product_id'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('ios_price')->label(__('wallet.packages.ios_price'))
                    ->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('ex_VAT')
                    ->label(__('wallet.packages.inc (VAT)', ['p' => Filament::getTenant()?->vat_percentage ?? 0]))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_best_value')->label(__('wallet.packages.best_value'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label(__('string.active'))
                    ->boolean(),
//                Tables\Columns\TextColumn::make('country.name')
//                    ->numeric()
//                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePackages::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public function getTitle(): string|Htmlable
    {
        return __('wallet.packages.single');
    }
}
