<?php

namespace App\Filament\Resources;

use App\Enums\Wallet\CreditType;
use App\Enums\Wallet\PremiumType;
use App\Filament\Resources\PricingPlanResource\Pages;
use App\Filament\Resources\PricingPlanResource\RelationManagers;
use App\Forms\Components\TranslatableGrid;
use App\Models\Currency;
use App\Models\PricingPlan;
use Blade;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class PricingPlanResource extends Resource
{
    protected static ?string $model = PricingPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $isScopedToTenant = true;
    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string
    {
        return __('wallet.payments.nav_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('wallet.plans.plural');
    }

    public static function getModelLabel(): string
    {
        return __('wallet.plans.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('wallet.plans.plural');
    }

    public static function canForceDelete(Model $record): bool
    {
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('wallet.plans.name'))
                    ->description(fn($record) => $record->description ?? "")
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label(__('string.active'))->boolean(),

                Tables\Columns\TextColumn::make('billing_cycles')->label(__('wallet.plans.billing_cycle'))
                    ->formatStateUsing(fn($state) => __('wallet.plans.billing_cycles.' . $state))->badge()
                    ->colors([
                        1 => 'warning',
                        0 => 'success',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('month_price')->label(__('subscriptions.price'))
                     ->numeric()
                    ->suffix(fn($record) => $record->currency?->symbol?? getCurrencySample() ?? "*")
                    ->sortable(),
                Tables\Columns\TextColumn::make('final_price')->label(__('wallet.packages.final_price'))
                    ->getStateUsing(fn($record)=>$record->getFinalPrice())
                    ->suffix(fn($record) => $record->currency?->symbol ?? getCurrencySample()?? "*")
                    ->sortable(false),
                Tables\Columns\IconColumn::make('ex_VAT') ->wrapHeader(true)->grow(false)
                    ->label(__('wallet.packages.inc (VAT)', ['p' => Filament::getTenant()?->vat_percentage ?? 0]))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_premium')->label(__('subscriptions.is_premium')),


                Tables\Columns\TextColumn::make('premium_type')->label(__('subscriptions.premium.title'))->badge()->size('sm')    ->searchable(),
                Tables\Columns\IconColumn::make('is_in_credit')->label(__('subscriptions.subscription_in_credit'))->wrapHeader(true)->grow(false),

                Tables\Columns\TextColumn::make('credit_type')->label(__('subscriptions.subscription_plans'))->badge()->size('sm')  ->searchable(),
                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->counts('subscriptions')
                    ->label(__('subscriptions.subscribers'))->badge()->size('sm'),


                Tables\Columns\TextColumn::make('currency.code')->label(__('wallet.currency'))
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

                Tables\Columns\IconColumn::make('is_best_value')->label(__('wallet.packages.best_value'))
                    ->boolean(),
                Tables\Columns\ColorColumn::make('bg_color')->label(__('wallet.packages.bg_color')),
                Tables\Columns\ColorColumn::make('text_color')->label(__('wallet.packages.text_color')),


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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->steps(self::getSteps())->skippableSteps(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record, $action) {
                        // Add your condition here
                        if ($record->subscriptions->isNotEmpty()) {
                            Notification::make()
                                ->title(__('subscriptions.cannot_delete') . ' ' . $record->name)
                                ->body(__('subscriptions.has_active_subscriptions'))
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    }),
//                Tables\Actions\ForceDeleteAction::make()->requiresConfirmation(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getSteps(): array
    {
        return
            [
                Wizard\Step::make(__('wallet.plans.description'))
                    ->schema([
                        TranslatableGrid::make()->textInput('name')->label(__('wallet.plans.name'))->required(),
                        TranslatableGrid::make()->textInput('description')->label(__('wallet.plans.description'))->required(),

                        Forms\Components\Split::make([
                            Forms\Components\Toggle::make('ex_VAT')->label(__('wallet.packages.ex (VAT)'))
                                ->default(true)->visible(false)
                                ->required(),
                            Forms\Components\Toggle::make('is_best_value')->label(__('wallet.packages.best_value'))
                                ->required(),
                            Forms\Components\Toggle::make('is_active')->label(__('string.active'))
                                ->required(),
                        ]),
                        Forms\Components\Split::make([
                            Forms\Components\ColorPicker::make('bg_color')->label(__('wallet.packages.bg_color'))->nullable(),
                            Forms\Components\ColorPicker::make('text_color')->label(__('wallet.packages.text_color'))->nullable(),
                        ])


                        /* Translatable::make(__('wallet.plans.tag'))
                             ->heading(null)
                             ->columns()
                             ->name('tag', __('wallet.plans.tag'))
                             ->label(__('wallet.plans.tag')),*/

                    ]),
                Wizard\Step::make(__('wallet.plans.features'))
                    ->schema([
                        Forms\Components\Split::make([
                            Forms\Components\Fieldset::make()
                                ->schema([

                                    Forms\Components\Toggle::make('is_premium')
                                        ->required()
                                        ->label(__('subscriptions.is_premium'))
                                        ->onColor('success')
                                        ->offColor('danger')
                                        ->onIcon('heroicon-m-user')
                                        ->offIcon('heroicon-m-user')
                                        ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="h-5 w-5" wire:loading wire:target="mountedActionsData.0.is_premium" />')))
                                        ->live()
                                        ->afterStateUpdated(function ($state, $component, $get, $set) {
//                                            if ($state === false) $set('premium_type', []); else $set('premium_type', PremiumType::getOptions());
//                                        $component->getContainer()->get('premium_type')->hidden($state);
                                        }),

                                    Forms\Components\Select::make('premium_type')
                                        ->hidden(fn($get) => $get('is_premium') === false || $get('is_premium') === null)
                                        ->label('Premium Type')->hiddenLabel()
                                        ->options(PremiumType::getOptions())
//                                        ->dehydrateStateUsing(fn() => PremiumType::IN_MAIN_CATEGORY  )
                                        ->default(fn() => PremiumType::IN_MAIN_CATEGORY  )
                                        ->required(fn($get) => !empty($get('is_premium'))),

                                    Forms\Components\TextInput::make('premium_items_limit')
                                        ->default(-1)

                                        ->hidden(fn($get) => $get('is_premium') === false || $get('is_premium') === null)
//                                        ->dehydrateStateUsing(fn($state) => -1)//$state ?? 0)
                                        ->label(__('subscriptions.main_category_limit'))
                                        ->helperText(__('subscriptions.features.hint_unlimited_use'))
                                        ->hiddenLabel()

                                        ->required(fn($get) => !empty($get('premium_type')))  ,


                                ])->label(__('subscriptions.premium.title'))->grow(),
                            Forms\Components\Fieldset::make()
                                ->schema([

                                    Forms\Components\Toggle::make('is_in_credit')
                                        ->columnSpanFull()
                                        ->required(true)
                                        ->label(__('subscriptions.subscription_in_credit'))
                                        ->onColor('success')
                                        ->offColor('danger')
                                        ->onIcon('heroicon-m-bolt')
                                        ->offIcon('heroicon-m-user')
                                        ->afterStateUpdated(function ($state, $component, $get, $set) {
//                                            if ($state === false) $set('credit_type', []); else $set('credit_type', PremiumType::getOptions());
                                        })
//                                        ->hint(new HtmlString(Blade::render('<x-filament::loading-indicator class="h-5 w-5" wire:loading wire:target="mountedActionsData.0.is_in_credit" />')))
                                        ->live(),

                                    Forms\Components\Select::make('credit_type')->hiddenLabel()

                                        ->hidden(fn($get) => $get('is_in_credit') === false || $get('is_in_credit') === null)
                                        ->label('credit Type')
//                                        ->dehydrateStateUsing(fn() => CreditType::IN_MAIN_CATEGORY  )
                                        ->default(fn() => CreditType::IN_MAIN_CATEGORY  )
                                        ->options(CreditType::getOptions())
                                        ->required(fn($get) => !empty($get('is_in_credit'))),

                                    Forms\Components\TextInput::make('credit_items_limit')
//                                        ->visible(false)
                                        ->hidden(fn($get) => $get('is_in_credit') === false || $get('is_in_credit') === null)
//                                        ->dehydrateStateUsing(fn($state) => -1)//$state ?? 0)
                                        ->default(-1)
                                        ->label(__('subscriptions.main_category_limit'))
                                        ->helperText(__('subscriptions.features.hint_unlimited_use'))
                                        ->hiddenLabel()->columnSpanFull()
                                        ->required(fn($get) => !empty($get('credit_type')))  ,

                                ])->label(__('subscriptions.subscription_in_credit'))->grow(),


                        ])->from('md'),

                        Forms\Components\Fieldset::make(__('wallet.plans.billing_cycle'))
                            ->columnSpanFull()
                            ->columns(2)
                            ->schema([

                                Forms\Components\ToggleButtons::make('billing_cycles')
                                    ->default(0)
                                    ->inline()
                                    ->label( __('subscriptions.billing_cycle') )
                                    ->inlineLabel()
                                    ->required()
                                    ->grouped()
                                    ->options([
                                        0 => __('subscriptions.monthly'),
                                        1 => __('subscriptions.yearly'),
                                    ]),

                                Forms\Components\TextInput::make('month_price')->label(__('subscriptions.price'))
                                    ->numeric() ->inlineLabel()
                                    ->live()
                                    ->requiredIf('billing_cycles', 0)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $price =$state;
                                        $exVAT =  $get('ex_VAT');
                                        $set('price_with_vat', $exVAT ? $price : priceWithVat($price));
                                    })
//                                            ->visible(fn($get) => $get('billing_cycles') == 0)
                                    ->suffix(fn() => Filament::getTenant()?->currency?->symbol ?? "*"),

                                Forms\Components\Select::make('currency_id')->label(__('wallet.currency'))
                                            ->hidden(true)
                                    ->required(false)
                                    ->default(Filament::getTenant()?->currency?->id ?? "")
                                    ->searchable()
                                    ->options(Currency::where('id', Filament::getTenant()?->currency?->id)->pluck('name', 'id')),

                                Forms\Components\ToggleButtons::make('ex_VAT')
                                    ->boolean()->grouped()
                                    ->label(__('wallet.packages.inc (VAT)', ['p' => Filament::getTenant()?->vat_percentage ?? 0]))
                                    ->default(true)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $price = $get('month_price');
                                        $exVAT = $state;//$get('ex_VAT');
                                        $set('price_with_vat', $exVAT ? $price : priceWithVat($price));
                                    }),
                                Forms\Components\TextInput::make('price_with_vat')
                                    ->inlineLabel()
                                    ->label(__('wallet.packages.final_price'))
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function ($state, $set, $get) {
                                        $price = $get('month_price');
                                        $exVAT = $get('ex_VAT');
                                        $set('price_with_vat', $exVAT ? $price : priceWithVat($price));
                                    }),

                                // iOS In-App Purchase Fields
                                Forms\Components\Section::make(__('wallet.packages.ios_settings'))
                                    ->description(__('wallet.packages.ios_settings_description'))
                                    ->schema([
                                        Forms\Components\TextInput::make('apple_product_id')
                                            ->label(__('wallet.packages.apple_product_id'))
                                            ->placeholder('jad_subscription_premium')
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
                            ]),

                    ]),


            ];

    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePricingPlans::route('/'),
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
        return __('wallet.plans.single');
    }
}
