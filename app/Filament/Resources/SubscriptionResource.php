<?php

namespace App\Filament\Resources;

use App\Concerns\SubscribeFrom;
use App\Enums\Wallet\CreditType;
use App\Enums\Wallet\PremiumType;
use App\Enums\Wallet\SubscriptionPlanType;
use App\Enums\Wallet\SubscriptionStatus;
use App\Filament\Resources\SubscriptionResource\Pages;
use App\Filament\Seller\Pages\SubscriptionPlans;
use App\Models\PaymentMethod;
use App\Models\PricingPlan;
use App\Models\Subscription;
use App\Services\Payment\PaymentService;
use App\Tables\Columns\SubscriptionFeaturesColumn;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static bool $isScopedToTenant = true;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('wallet.payments.nav_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('subscriptions.subscribers');
    }

    public static function getModelLabel(): string
    {
        return __('subscriptions.subscriber');
    }

    public static function getPluralLabel(): ?string
    {
        return __('subscriptions.subscribers');
    }

    public static function table(Table $table): Table
    {
        $is_admin = filament()->getAuthGuard() == 'admin';

        return $table
            ->striped()
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeCells)
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('#'))->size('xs'),
                Tables\Columns\TextColumn::make('subscribe_at')->label(__('subscriptions.subscribe_at'))->size('xs')
                    ->dateTime('d-m-Y H:i a')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->label(__('subscriptions.end_at'))->size('xs')
                    ->dateTime('d-m-Y H:i a')
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan.name')->label(__('subscriptions.pricing_plans'))->size('sm')
                    ->sortable(),
                Tables\Columns\TextColumn::make('seller.name')->label(__('subscriptions.subscriber'))->size('sm')->visible($is_admin)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->label(__('subscriptions.subscription_status'))->wrapHeader()->grow(false)
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')->label(__('subscriptions.price'))
                    ->suffix(fn ($record, $state) => $record?->plan?->currency?->symbol)
                    ->sortable(),

                Tables\Columns\ColumnGroup::make('in_premium', [
                    Tables\Columns\IconColumn::make('is_premium')->label(__('subscriptions.is_subscribed'))
                        ->boolean(),
                    //                    Tables\Columns\TextColumn::make('premium_type')->size('sm')->label(__('wallet.plans.features'))->badge()->searchable(),
                    Tables\Columns\TextColumn::make('premium_items_limit')
                        ->formatStateUsing(fn ($record, $state) => $record->premium_items_limit === -1 ? __('subscriptions.is_unlimited') : (empty($state) ? '-' : $state))
                        ->label(__('subscriptions.limit')),
                    //
                    //                    SubscriptionFeaturesColumn::make('premium_type')->grow(false)->wrapHeader(false)
                    //                        ->label(__('wallet.plans.features'))
                    //                        ->searchable(false)->setType(SubscriptionPlanType::PREMIUM->value),

                ])->label(__('subscriptions.premium.title')),

                Tables\Columns\ColumnGroup::make('in_credit', [
                    Tables\Columns\IconColumn::make('is_in_credit')->label(__('subscriptions.is_subscribed'))->wrap(false)
                        ->boolean(),

                    Tables\Columns\TextColumn::make('credit_items_limit')
                        ->formatStateUsing(fn ($record, $state) => $record->credit_items_limit === -1 ? __('subscriptions.is_unlimited') : (empty($state) ? '-' : $state))
                        ->label(__('subscriptions.limit'))->wrap(false)->grow(false),

                    //                    SubscriptionFeaturesColumn::make('credit_type')
                    //                        ->label(__('wallet.plans.features'))
                    //                        ->setType(SubscriptionPlanType::CREDIT->value)
                    //                        ->searchable(),

                ])->label(__('subscriptions.subscription_in_credit')),

                Tables\Columns\IconColumn::make('payment_status')->label(__('subscriptions.payment_status'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_monthly')->label(__('subscriptions.is_monthly'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('next_renew_at')->label(__('subscriptions.next_renew_at'))->size('xs')
                    ->dateTime('d-m-Y H:i a')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paymentMethod.name')->label(__('subscriptions.payment_method'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('trans_ref')->label(__('wallet.purchase_string.transaction_id'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('canceled_at')->label(__('subscriptions.canceled_at'))
                    ->dateTime('d-m-Y H:i a')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d-m-Y H:i a')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d-m-Y H:i a')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(__('subscriptions.subscription_status'))
                    ->multiple()
                    ->options(SubscriptionStatus::getOptions()),
                Tables\Filters\Filter::make('is_expired')
                    ->modifyBaseQueryUsing(fn ($query) => $query->expired())
                    ->label(__('subscriptions.subscription_expired'))
                    ->toggle(),

            ], layout: Tables\Enums\FiltersLayout::Dropdown)
            ->actions([
                Tables\Actions\ActionGroup::make(self::getTableActions())])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getTableActions(): array
    {
        $isAdmin = filament()->getAuthGuard() === 'admin';

        return [
            Tables\Actions\Action::make('change_items')
                ->label(__('subscriptions.change_items'))
                ->icon('heroicon-o-adjustments-horizontal')
                ->visible($isAdmin)
                ->modalHeading(__('subscriptions.change_items'))
                ->color('primary')
                ->form(fn (Subscription $record) => SubscribeFrom::getRenewForm($record, includePaymentSelector: false))
                ->action(function (Subscription $record, array $data, Tables\Actions\Action $action) {
                    try {
                        $items = [];

                        foreach (['premium' => PremiumType::class, 'credit' => CreditType::class] as $type => $enumClass) {
                            $typeValue = SubscriptionPlanType::from($type)->value;

                            foreach ($enumClass::cases() as $enum) {
                                $column = $enum->getColumnName();

                                foreach ($data[$typeValue][$column] ?? [] as $id) {
                                    if (! $id) {
                                        continue;
                                    }

                                    $items[] = [
                                        'subscription_id' => $record->id,
                                        $column => $id,
                                        'quantity' => 1,
                                        'type' => $typeValue,
                                    ];
                                }
                            }
                        }

                        $record->items()->delete();

                        if (! empty($items)) {
                            $record->items()->createMany($items);
                        }

                        Notification::make()
                            ->title(__('subscriptions.change_items_success'))
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        report($exception);

                        $action->failure();

                        Notification::make()
                            ->title(__('subscriptions.change_items_failed'))
                            ->danger()
                            ->send();
                    }
                }),
            Tables\Actions\Action::make('cancel_sub')->label(__('subscriptions.cancel_action'))
                ->icon('heroicon-o-x-circle')
                ->hidden(fn ($record) => $record->isCanceled())
                ->requiresConfirmation()
                ->color('danger')
                ->action(function (Model $record) {
                    $record->canceling();
                }),

            Tables\Actions\Action::make('renew_sub')->label(__('subscriptions.renew_action'))
                ->icon('heroicon-o-arrow-path')
                ->hidden(fn ($record) => ! $record->renewable())
                ->extraModalFooterActions(

                    fn () => [
                        Tables\Actions\Action::make('change Plan')->label(__('subscriptions.change_plan'))
                            ->action(function ($record) {
                                Subscription::where('seller_id', $record->seller_id)
                                    ->expired()
                                    ->update([
                                        'status' => SubscriptionStatus::CANCELLED,
                                        'canceled_at' => Carbon::now(),
                                    ]);

                                return redirect()->to(SubscriptionPlans::getUrl());

                            }),
                    ])

//                ->requiresConfirmation()
                ->form(fn ($record) => SubscribeFrom::getRenewForm($record))
                ->color('success')
                ->action(function (Model $record, $data, Tables\Actions\Action $action) {
                    $subscription = $record;
                    if ($subscription->seller_id != auth(filament()->getAuthGuard())->user()->id) {
                        $action->failure();
                    }
                    $items = [];
                    foreach (['premium' => PremiumType::class, 'credit' => CreditType::class] as $type => $enumClass) {
                        $typeValue = SubscriptionPlanType::from($type)->value;
                        foreach ($enumClass::cases() as $enum) {
                            $column = $enum->getColumnName();
                            foreach ($data[$typeValue][$column] ?? [] as $id) {
                                if ($id) {
                                    $items[] = [
                                        'subscription_id' => $subscription->id,
                                        $column => $id,
                                        'quantity' => 1,
                                        'type' => $typeValue,
                                    ];
                                }

                            }
                        }
                    }

                    if (! empty($items)) {
                        $subscription->items()->delete();
                        $subscription->items()->createMany($items);
                    }

                    $user =
                    $package = PricingPlan::withTrashed()->find($subscription->price_plan_id);
                    $paymentMethod = PaymentMethod::find($data['payment'] ?? 2);
                    $paymentClass = PaymentService::getPayment($paymentMethod->type);
                    $final_price = $subscription->total_price;

                    $paymentGateway = new $paymentClass($paymentMethod->details);

                    $url = $paymentGateway->createPayment(
                        paymentMethodId: $paymentMethod->id,
                        customer: auth(filament()->getAuthGuard())->user(),
                        product: $package,
                        amount: $final_price,

                        country_id: $user->country_id,
                        currency: 'SAR',
                        subscription: $subscription
                    );

                }),

            //            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('price_plan_id')
                    ->relationship('plan', 'name')
                    ->label(__('subscriptions.pricing_plans')),
                Forms\Components\Select::make('seller_id')->label(__('subscriptions.subscriber'))
                    ->relationship('seller', 'name')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->required()
                    ->label(__('subscriptions.subscription_status'))
                    ->options(SubscriptionStatus::getOptions()),
                Forms\Components\TextInput::make('total_price')->label(__('subscriptions.price'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_premium')->label(__('subscriptions.is_premium'))
                    ->required(),
                Forms\Components\Select::make('premium_type')->label(__('subscriptions.premium.title'))->inlineLabel()
                    ->options(PremiumType::getOptions())
                    ->default(null),
                Forms\Components\Toggle::make('is_in_credit')->label(__('subscriptions.subscription_in_credit'))
                    ->required(),
                Forms\Components\Select::make('credit_type')->inlineLabel()
                    ->label(__('wallet.plans.features'))
                    ->options(CreditType::getOptions())
                    ->default(null),
                Forms\Components\TextInput::make('premium_items_limit')
                    ->label(__('subscriptions.premium_limit'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('credit_items_limit')
                    ->label(__('subscriptions.subscription_limit'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_auto_renew')->label(__('subscriptions.is_auto_renew'))
                    ->required(),

                Forms\Components\Toggle::make('payment_status')->label(__('subscriptions.payment_status'))
                    ->required(),
                Forms\Components\DateTimePicker::make('canceled_at')->label(__('subscriptions.canceled_at')),
                Forms\Components\DateTimePicker::make('ends_at')->label(__('subscriptions.end_at')),
                Forms\Components\DateTimePicker::make('subscribe_at')->label(__('subscriptions.subscribe_at')),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSubscriptions::route('/'),
        ];
    }

    //    public static function getEloquentQuery(): Builder
    //    {
    //        return parent::getEloquentQuery()->with('items'); // TODO: Change the autogenerated stub
    //    }
}
