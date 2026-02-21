<?php

namespace App\Concerns;

use App\Enums\Wallet\CreditType;
use App\Enums\Wallet\PremiumType;
use App\Enums\Wallet\SubscriptionPlanType;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\Seller;
use App\Models\Service;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Tables;

class SubscribeFrom
{
    public static function createPurchase($price,
        $item,
        $payment,
        $payable,
        $transaction_id = null,
        $chargeable = null,
        $payment_detail_id = null,
        $is_form_wallet = 0,
        $currency = null,
        $country_id = null,
        $status = 1)
    {

        $purchase = new Purchase;
        $purchase->amount = $price;
        $purchase->transaction_id = $transaction_id;
        $purchase->status = $status;
        $purchase->is_form_wallet = $is_form_wallet ?? 0;
        $purchase->payment = $payment;
        $purchase->payment_detail_id = $payment_detail_id;
        $purchase->currency = $currency ?? null;
        $purchase->country_id = $country_id ?? null;

        $purchase->payable()->associate($payable);
        if ($item) {
            $purchase->purchasable()->associate($item);
        }
        if ($chargeable) {
            $purchase->chargeable()->associate($chargeable);
        }

        $purchase->save();

    }

    public static function getForm($plan, $premiumType, $creditType): array
    {
        $sellerContext = self::sellerContext();

        return [
            Grid::make(2)->schema([

                Section::make('')
                    ->columnSpan($plan->is_in_credit ? 1 : 'full')
                    ->visible($plan->is_premium)
                    ->statePath($premiumType)
                    ->schema([
                        Placeholder::make('is_unlimited')->content(fn () => $plan->premium_items_limit == -1 ? __('subscriptions.premium.unlimited') : '')
                            ->hidden($plan->premium_items_limit != -1)
                            ->hiddenLabel(),

                        Select::make(PremiumType::IN_MAIN_CATEGORY->getColumnName())
                            ->searchable()
                            ->required($plan->premium_items_limit != -1)
                            ->multiple()
                            ->maxItems($plan->premium_items_limit ?? null)
                            ->visible($plan->premium_type?->getColumnName() == 'main_category_id' && $plan->premium_items_limit != -1)
                            ->options(fn () => self::categoryOptions(
                                ids: $sellerContext['mainCategoryIds'],
                                isMain: true,
                                type: $premiumType,
                            ))
                            ->label(__('subscriptions.choose_main_category')),

                        Select::make(PremiumType::IN_SUB_CATEGORY->getColumnName())
                            ->searchable()
                            ->required($plan->premium_items_limit != -1)
//                            ->multiple(fn() => $plan->premium_items_limit != 1)
                            ->multiple()
                            ->visible($plan->premium_type?->getColumnName() == 'sub_category_id' && $plan->premium_items_limit != -1)
                            ->maxItems($plan->premium_items_limit ?? null)
                            ->options(fn () => self::categoryOptions(
                                ids: $sellerContext['subCategoryIds'],
                                isMain: false,
                                type: $premiumType,
                            ))
                            ->label(__('subscriptions.choose_sub_category')),

                        Select::make(PremiumType::IN_SERVICE->getColumnName())
                            ->searchable()
                            ->required($plan->premium_items_limit != -1)
//                            ->multiple(fn() => $plan->premium_items_limit != 1)
                            ->multiple()
                            ->visible($plan->premium_type?->getColumnName() == 'service_id' && $plan->premium_items_limit != -1)
                            ->maxItems($plan->premium_items_limit ?? null)
                            ->options(fn () => self::serviceOptions(
                                serviceIds: $sellerContext['serviceIds'],
                                type: $premiumType,
                            ))
                            ->label(__('labels.service_name')),
                    ]),
                Section::make('')
                    ->columnSpan($plan->is_premium ? 1 : 'full')
                    ->visible($plan->is_in_credit)
                    ->statePath($creditType)
                    ->schema([
                        Placeholder::make('is_unlimited')->content(fn () => $plan->credit_items_limit == -1 ? __('subscriptions.unlimited_credit_subscription') : $plan->credit_items_limit)
                            ->hidden($plan->credit_items_limit != -1)
                            ->hiddenLabel(),

                        Select::make(CreditType::IN_MAIN_CATEGORY->getColumnName())
                            ->searchable()
                            ->required(! $plan->credit_items_limit == -1)
                            ->multiple()
                            ->maxItems($plan->credit_items_limit ?? null)
                            ->visible($plan->credit_type?->getColumnName() == 'main_category_id' && $plan->credit_items_limit != -1)
                            ->options(fn () => self::categoryOptions(
                                ids: $sellerContext['mainCategoryIds'],
                                isMain: true,
                                type: $creditType,
                            ))
                            ->label(__('subscriptions.choose_main_category')),

                        Select::make(CreditType::IN_SUB_CATEGORY->getColumnName())
                            ->searchable()
                            ->required(! $plan->credit_items_limit == -1)
//                            ->multiple(fn() => $plan->credit_items_limit != 1)
                            ->multiple()
                            ->visible($plan->credit_type?->getColumnName() == 'sub_category_id' && $plan->credit_items_limit != -1)
                            ->maxItems($plan->credit_items_limit ?? null)
                            ->options(fn () => self::categoryOptions(
                                ids: $sellerContext['subCategoryIds'],
                                isMain: false,
                                type: $creditType,
                            ))
                            ->label(__('subscriptions.choose_sub_category')),

                        Select::make(CreditType::IN_SERVICE->getColumnName())
                            ->searchable()
                            ->required(! $plan->credit_items_limit == -1)
                            ->multiple()
                            ->visible($plan->credit_type?->getColumnName() == 'service_id' && $plan->credit_items_limit != -1)
                            ->maxItems($plan->credit_items_limit ?? 0)
                            ->options(fn () => self::serviceOptions(
                                serviceIds: $sellerContext['serviceIds'],
                                type: $creditType,
                            ))
                            ->label(__('labels.service_name')),
                    ]),

            ]),
            Radio::make('payment')
                ->label(__('wallet.payments.select'))
                ->inline()
                ->inlineLabel(false)
                ->options(PaymentMethod::select('id', 'name', 'logo', 'type')->where('active', 1)->get()->pluck('name_html', 'id'))
                ->required(),
        ];
    }

    public static function getRenewForm($record, bool $includePaymentSelector = true): array
    {
        $sellerContext = self::sellerContext($record?->seller);

        $schema = [
            Section::make()
                ->columns(2)
                ->inlineLabel()
                ->schema([
                    Placeholder::make('price_plan_id')->inlineLabel()->content(fn ($record) => $record?->plan?->name)->label(__('subscriptions.pricing_plans')),
                    Placeholder::make('total_price')->label(__('subscriptions.price'))->content($record->total_price.' '.$record?->plan?->currency?->symbol),
                ]),

            Grid::make(2)->schema([

                Section::make(__('subscriptions.premium.title'))
                    ->columnSpan($record->is_in_credit ? 1 : 'full')
                    ->visible($record->is_premium)
                    ->statePath(SubscriptionPlanType::PREMIUM->value)
                    ->schema([
                        Placeholder::make('is_unlimited')->content(fn () => $record->premium_items_limit == -1 ? __('subscriptions.is_unlimited') : '')
                            ->hidden($record->premium_items_limit != -1)
                            ->hiddenLabel(),

                        Select::make(PremiumType::IN_MAIN_CATEGORY->getColumnName())
                            ->formatStateUsing(fn ($record) => $record->items()
                                ->where('type', SubscriptionPlanType::PREMIUM)
                                ->pluck('main_category_id')
                                ->filter()
                                ->values()
                                ->all())
                            ->searchable()
                            ->required($record->premium_items_limit != -1)
                            ->multiple()
                            ->maxItems($record->premium_items_limit ?? null)
                            ->visible($record->premium_type?->getColumnName() == 'main_category_id' && $record->premium_items_limit != -1)
                            ->options(fn () => self::categoryOptions(
                                ids: $sellerContext['mainCategoryIds'],
                                isMain: true,
                                type: null,
                                useUnsubscribed: false,
                            ))
                            ->label(__('subscriptions.choose_main_category')),

                        Select::make(PremiumType::IN_SUB_CATEGORY->getColumnName())
                            ->formatStateUsing(fn ($record) => $record->items()
                                ->where('type', SubscriptionPlanType::PREMIUM)
                                ->pluck('sub_category_id')
                                ->filter()
                                ->values()
                                ->all())
                            ->searchable()
                            ->required($record->premium_items_limit != -1)
//                                        ->multiple(fn() => $record->premium_items_limit != 1)
                            ->multiple()
                            ->visible($record->premium_type?->getColumnName() == 'sub_category_id' && $record->premium_items_limit != -1)
                            ->maxItems($record->premium_items_limit ?? null)
                            ->options(fn () => self::categoryOptions(
                                ids: $sellerContext['subCategoryIds'],
                                isMain: false,
                                type: null,
                                useUnsubscribed: false,
                            ))
                            ->label(__('subscriptions.choose_sub_category')),

                        Select::make(PremiumType::IN_SERVICE->getColumnName())
                            ->formatStateUsing(fn ($record) => $record->items()
                                ->where('type', SubscriptionPlanType::PREMIUM)
                                ->pluck('service_id')
                                ->filter()
                                ->values()
                                ->all())
                            ->searchable()
                            ->required($record->premium_items_limit != -1)
//                                        ->multiple(fn() => $record->premium_items_limit != 1)
                            ->multiple()
                            ->visible($record->premium_type?->getColumnName() == 'service_id' && $record->premium_items_limit != -1)
                            ->maxItems($record->premium_items_limit ?? null)
                            ->options(fn () => self::serviceOptions(
                                serviceIds: $sellerContext['serviceIds'],
                                type: null,
                                useUnsubscribed: false,
                            ))
                            ->label(__('labels.service_name')),
                    ]),
                Section::make(__('subscriptions.subscription_in_credit'))
                    ->columnSpan($record->is_premium ? 1 : 'full')
                    ->visible($record->is_in_credit)
                    ->statePath(SubscriptionPlanType::CREDIT->value)
                    ->schema([
                        Placeholder::make('is_unlimited')->content(fn () => $record->credit_items_limit == -1 ? __('subscriptions.is_unlimited') : $record->credit_items_limit)
                            ->hidden($record->credit_items_limit != -1)
                            ->hiddenLabel(),

                        Select::make(CreditType::IN_MAIN_CATEGORY->getColumnName())
                            ->formatStateUsing(fn ($record) => $record->items()
                                ->where('type', SubscriptionPlanType::CREDIT)
                                ->pluck('main_category_id')
                                ->filter()
                                ->values()
                                ->all())
                            ->searchable()
                            ->required(! $record->credit_items_limit == -1)
                            ->multiple()
                            ->maxItems($record->credit_items_limit ?? null)
                            ->visible($record->credit_type?->getColumnName() == 'main_category_id' && $record->credit_items_limit != -1)
                            ->options(fn () => self::categoryOptions(
                                ids: $sellerContext['mainCategoryIds'],
                                isMain: true,
                                type: SubscriptionPlanType::CREDIT->value,
                                useUnsubscribed: true,
                            ))
                            ->label(__('subscriptions.choose_main_category')),

                        Select::make(CreditType::IN_SUB_CATEGORY->getColumnName())
                            ->formatStateUsing(fn ($record) => $record->items()
                                ->where('type', SubscriptionPlanType::CREDIT)
                                ->pluck('sub_category_id')
                                ->filter()
                                ->values()
                                ->all())
                            ->searchable()
                            ->required(! $record->credit_items_limit == -1)
//                                        ->multiple(fn() => $record->credit_items_limit != 1)
                            ->multiple()
                            ->visible($record->credit_type?->getColumnName() == 'sub_category_id' && $record->credit_items_limit != -1)
                            ->maxItems($record->credit_items_limit ?? null)
                            ->options(fn () => self::categoryOptions(
                                ids: $sellerContext['subCategoryIds'],
                                isMain: false,
                                type: SubscriptionPlanType::CREDIT->value,
                                useUnsubscribed: true,
                            ))
                            ->label(__('subscriptions.choose_sub_category')),

                        Select::make(CreditType::IN_SERVICE->getColumnName())
                            ->formatStateUsing(fn ($record) => $record->items()
                                ->where('type', SubscriptionPlanType::CREDIT)
                                ->pluck('service_id')
                                ->filter()
                                ->values()
                                ->all())
                            ->searchable()
                            ->required(! $record->credit_items_limit == -1)
//                                        ->multiple(fn() => $record->credit_items_limit != 1)
                            ->multiple()
                            ->visible($record->credit_type?->getColumnName() == 'service_id' && $record->credit_items_limit != -1)
                            //  ->rules(['array', "max:{$record->credit_items_limit}"])
                            ->maxItems($record->credit_items_limit ?? 0)
                            ->options(fn () => self::serviceOptions(
                                serviceIds: $sellerContext['serviceIds'],
                                type: SubscriptionPlanType::CREDIT->value,
                                useUnsubscribed: true,
                            ))
                            ->label(__('labels.service_name')),
                    ]),

            ]),
        ];

        if ($includePaymentSelector) {
            $schema[] = Radio::make('payment')
                ->label(__('wallet.payments.select'))
                ->inline()
                ->inlineLabel(false)
                ->options(PaymentMethod::select('id', 'name', 'logo', 'type')->where('active', 1)->get()->pluck('name_html', 'id'))
                ->required();
        }

        return [
            Grid::make(2)
                ->inlineLabel()
                ->schema(array_values(array_filter($schema, fn ($component) => $component !== null))),
        ];
    }

    public static function getPurchaseColumns(): array
    {
        return [
            //                Tables\Columns\TextColumn::make('id')
            //                    ->label('ID')
            //                    ->searchable(),
            Tables\Columns\TextColumn::make('payable.name')->size('xs')
                ->label(__('wallet.payable'))
                ->searchable(),

            Tables\Columns\TextColumn::make('purchasable.getPaymentTitle()')->size('xs')
                ->getStateUsing(fn ($record) => $record->purchasable ? (method_exists($record->purchasable, 'getPaymentTitle') ? $record->purchasable?->getPaymentTitle() : '-') : ' ')
                ->label(__('wallet.purchase_string.purchasable'))
                ->searchable(false),

            Tables\Columns\TextColumn::make('amount')->size('sm')
                ->label(__('wallet.purchase_string.amount'))
                ->suffix(fn ($record) => ' '.$record->getCurrency())
                ->sortable(),
            Tables\Columns\IconColumn::make('status')
                ->label(__('wallet.payment_status.single'))
                ->boolean()
                ->sortable(),

            Tables\Columns\IconColumn::make('is_form_wallet')
                ->label(__('wallet.purchase_string.is_form_wallet'))
                ->boolean(),
            Tables\Columns\TextColumn::make('charge')->size('xs')
                ->label(__('wallet.purchase_string.charge'))
                ->getStateUsing(fn ($record) => $record->getCharge()),
            Tables\Columns\TextColumn::make('payment')->badge()
                ->label(__('wallet.purchase_string.payment'))
                ->color(static function ($state): string {
                    if ($state === 'wallet') {
                        return 'success';
                    }

                    return 'primary';
                }),

            Tables\Columns\TextColumn::make('previous_tran_ref')->size('xs')
                ->label(__('subscriptions.previous_tran_ref'))
                ->searchable(),
            Tables\Columns\TextColumn::make('transaction_id')->size('xs')
                ->label(__('subscriptions.trans_ref'))
                ->searchable(),
            Tables\Columns\IconColumn::make('request_refund')->label(__('subscriptions.request_refund'))->boolean()->size('sm'),
            Tables\Columns\IconColumn::make('is_refund')->label(__('subscriptions.refund'))->boolean()->size('sm'),
            Tables\Columns\TextColumn::make('refund_amount')->label(__('subscriptions.refund_amount')),
            Tables\Columns\TextColumn::make('paymentDetails.refund_reason')->label(__('subscriptions.refund_reason'))->size('xs'),
            Tables\Columns\TextColumn::make('refund_response_message')->label(__('subscriptions.refund_process'))->size('xs'),

            Tables\Columns\TextColumn::make('created_at')->size('xs')
                ->label(__('wallet.purchase_string.created_at'))
                ->dateTime()
                ->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->size('xs')
                ->label(__('wallet.purchase_string.updated_at'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected static function sellerContext(?Seller $seller = null): array
    {
        $seller ??= self::resolveSeller();

        if (! $seller) {
            return [
                'seller' => null,
                'serviceIds' => [],
                'mainCategoryIds' => [],
                'subCategoryIds' => [],
            ];
        }

        $serviceIds = $seller->services()->pluck('services.id')->unique()->filter()->values()->all();

        if (empty($serviceIds)) {
            return [
                'seller' => $seller,
                'serviceIds' => [],
                'mainCategoryIds' => [],
                'subCategoryIds' => [],
            ];
        }

        $categoryIds = Service::query()->whereIn('id', $serviceIds)->pluck('category_id')->filter()->unique();

        if ($categoryIds->isEmpty()) {
            return [
                'seller' => $seller,
                'serviceIds' => $serviceIds,
                'mainCategoryIds' => [],
                'subCategoryIds' => [],
            ];
        }

        $categories = Category::query()
            ->whereIn('id', $categoryIds)
            ->get(['id', 'parent_id']);

        $mainCategoryIds = $categories
            ->map(fn (Category $category) => $category->parent_id ?: $category->id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $subCategoryIds = $categories
            ->filter(fn (Category $category) => $category->parent_id !== null)
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        return [
            'seller' => $seller,
            'serviceIds' => $serviceIds,
            'mainCategoryIds' => $mainCategoryIds,
            'subCategoryIds' => $subCategoryIds,
        ];
    }

    protected static function resolveSeller(): ?Seller
    {
        $sellerGuardUser = auth('seller')->user();

        if ($sellerGuardUser instanceof Seller) {
            return $sellerGuardUser;
        }

        $defaultUser = auth()->user();

        return $defaultUser instanceof Seller ? $defaultUser : null;
    }

    protected static function categoryOptions(array $ids, bool $isMain, ?string $type = null, bool $useUnsubscribed = true): array
    {
        if (empty($ids)) {
            return [];
        }

        $shouldUseUnsubscribed = $useUnsubscribed && $type !== null && auth('seller')->check();

        $query = $shouldUseUnsubscribed
            ? Category::unsubscribed($type)->whereActive(true)
            : Category::query()->whereActive(true);

        $query = $isMain
            ? $query->whereNull('parent_id')
            : $query->whereNotNull('parent_id');

        $query->whereIn('id', $ids);

        return $query
            ->pluck('name', 'id')
            ->mapWithKeys(fn ($name, $id) => [
                $id => filled($name) ? (string) $name : sprintf('%s (#%s)', __('labels.na'), $id),
            ])
            ->toArray();
    }

    protected static function serviceOptions(array $serviceIds, ?string $type = null, bool $useUnsubscribed = true): array
    {
        if (empty($serviceIds)) {
            return [];
        }

        $shouldUseUnsubscribed = $useUnsubscribed && $type !== null && auth('seller')->check();

        $query = $shouldUseUnsubscribed
            ? Service::unsubscribed($type)->whereActive(true)
            : Service::query()->whereActive(true);

        $query->whereIn('id', $serviceIds);

        return $query
            ->pluck('name', 'id')
            ->mapWithKeys(fn ($name, $id) => [
                $id => filled($name) ? (string) $name : sprintf('%s (#%s)', __('labels.na'), $id),
            ])
            ->toArray();
    }
}
