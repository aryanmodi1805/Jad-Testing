<?php

namespace App\Http\Controllers;

use App\Concerns\ApiResponseFormat;
use App\Enums\Wallet\CreditType;
use App\Enums\Wallet\PremiumType;
use App\Enums\Wallet\SubscriptionPlanType;
use App\Enums\Wallet\SubscriptionStatus;
use App\Models\Category;
use App\Models\Package;
use App\Models\PricingPlan;
use App\Models\Purchase;
use App\Models\Seller;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\Scopes\TenantScope;
use App\Services\AppleInAppPurchaseService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IosInAppPurchaseController extends Controller
{
    use ApiResponseFormat;

    public function __construct(private AppleInAppPurchaseService $appleInAppPurchaseService)
    {
        $this->middleware('auth:sanctum')->only(['getProducts', 'validatePurchase']);
    }

    /**
     * Get all available iOS products (packages and subscription plans)
     */
    public function getProducts(Request $request): JsonResponse
    {
        try {
            /** @var Seller|null $seller */
            $seller = $request->user();

            if (! $seller instanceof Seller) {
                return $this->ApiResponseFormatted(
                    401,
                    null,
                    trans('api.unauthorized'),
                    request: $request
                );
            }

            $packages = Package::query()
                ->where('is_active', true)
                ->where('is_ios_active', true)
                ->whereNotNull('apple_product_id')
                ->when($seller->country_id, function ($query, $countryId) {
                    $query->where(function ($q) use ($countryId) {
                        $q->whereNull('country_id')
                            ->orWhere('country_id', $countryId);
                    });
                })
                ->with(['currency'])
                ->orderByDesc('is_best_value')
                ->orderBy('price')
                ->get()
                ->map(fn (Package $package) => $this->formatPackageForResponse($package))
                ->values()
                ->all();

            $subscriptions = PricingPlan::query()
                ->where('is_active', true)
                ->where('is_ios_active', true)
                ->whereNotNull('apple_product_id')
                ->when($seller->country_id, function ($query, $countryId) {
                    $query->where(function ($q) use ($countryId) {
                        $q->whereNull('country_id')
                            ->orWhere('country_id', $countryId);
                    });
                })
                ->with(['currency'])
                ->orderByDesc('is_best_value')
                ->orderBy('month_price')
                ->get()
                ->map(fn (PricingPlan $plan) => $this->formatSubscriptionForResponse($plan, $seller))
                ->values()
                ->all();

            $data = [
                'packages' => $packages,
                'subscriptions' => $subscriptions,
            ];

            return $this->ApiResponseFormatted(200, $data, trans('api.products_retrieved_successfully'), request: $request);
        } catch (\Exception $e) {
            Log::error('iOS Products Error: '.$e->getMessage());

            return $this->ApiResponseFormatted(500, null, trans('api.failed_to_retrieve_products'), request: $request);
        }
    }

    /**
     * Validate and process iOS purchase with StoreKit 2 signed transaction
     */
    public function validatePurchase(Request $request): JsonResponse
    {
        Log::channel('IOS')->info('iOS Purchase Validation Request', $request->all());
        $validator = Validator::make($request->all(), [
            'signed_transaction' => 'required|string',
            'product_id' => 'required|string',
            'transaction_id' => 'required|string',
            'product_type' => 'required|in:package,subscription',
            'item_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->ApiResponseFormatted(
                422,
                [
                    'success' => false,
                    'message' => trans('api.validation_error'),
                    'errors' => $validator->errors()->messages(),
                ],
                trans('api.validation_error'),
                request: $request
            );
        }

        $productId = $request->input('product_id');
        $transactionId = $request->input('transaction_id');

        try {
            $user = Auth::user();

            if (! $user) {
                return $this->ApiResponseFormatted(
                    401,
                    [
                        'success' => false,
                        'message' => trans('api.unauthorized'),
                    ],
                    trans('api.unauthorized'),
                    request: $request
                );
            }

            $signedTransaction = $request->input('signed_transaction');
            $productType = $request->input('product_type');
            $itemId = (int) $request->integer('item_id');

            // Check if transaction already processed
            $existingPurchase = Purchase::where('transaction_id', $transactionId)->first();
            if ($existingPurchase) {
                return $this->ApiResponseFormatted(
                    409,
                    [
                        'success' => false,
                        'message' => trans('api.transaction_already_processed'),
                    ],
                    trans('api.transaction_already_processed'),
                    request: $request
                );
            }

            $verification = $this->appleInAppPurchaseService->verifyTransaction(
                $signedTransaction,
                $transactionId,
                $productId,
                $request->input('environment')
            );
            Log::channel('IOS')->info('Apple Transaction Verification Response', $verification);

            if (! $verification['valid']) {
                $errorKey = $verification['error'] ?? 'purchase_validation_failed';
                $messageKey = Lang::has("api.$errorKey") ? "api.$errorKey" : 'api.purchase_validation_failed';
                $statusCode = $errorKey === 'app_store_not_configured' ? 500 : 400;

                return $this->ApiResponseFormatted(
                    $statusCode,
                    [
                        'success' => false,
                        'message' => trans($messageKey),
                        'error_code' => $errorKey,
                    ],
                    trans($messageKey),
                    request: $request
                );
            }

            // Verify product ID matches
            $verifiedProductId = $verification['product_id'] ?? null;
            Log::channel('IOS')->info('Transaction Product ID', ['verified_product_id' => $verifiedProductId, 'expected_product_id' => $productId]);
            if ($verifiedProductId !== $productId) {
                return $this->ApiResponseFormatted(
                    400,
                    [
                        'success' => false,
                        'message' => trans('api.product_id_mismatch'),
                    ],
                    trans('api.product_id_mismatch'),
                    request: $request
                );
            }

            // Verify transaction ID matches
            $verifiedTransactionId = $verification['transaction_id'] ?? null;
            if ($verifiedTransactionId !== $transactionId) {
                return $this->ApiResponseFormatted(
                    400,
                    [
                        'success' => false,
                        'message' => trans('api.transaction_id_mismatch'),
                    ],
                    trans('api.transaction_id_mismatch'),
                    request: $request
                );
            }

            // Process the purchase based on type
            if ($productType === 'package') {
                return $this->processPackagePurchase($user, $itemId, $transactionId, $verification, $request);
            }

            $plan = $this->findPlanForPurchase($user, $itemId, $productId);

            if (! $plan) {
                return $this->ApiResponseFormatted(
                    404,
                    [
                        'success' => false,
                        'message' => trans('api.subscription_not_available_for_ios'),
                    ],
                    trans('api.subscription_not_available_for_ios'),
                    request: $request
                );
            }

            $selectionData = $this->prepareSubscriptionSelections($plan, $request, $user);

            return $this->processSubscriptionPurchase($user, $plan, $transactionId, $verification, $request, $selectionData);
        } catch (\Exception $e) {
            Log::error('iOS Purchase Validation Error: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'product_id' => $productId ?? null,
                'transaction_id' => $transactionId ?? null,
            ]);

            return $this->ApiResponseFormatted(
                500,
                [
                    'success' => false,
                    'message' => trans('api.purchase_validation_failed'),
                ],
                trans('api.purchase_validation_failed'),
                request: $request
            );
        }
    }

    /**
     * Process package purchase
     */
    private function processPackagePurchase($user, $packageId, $transactionId, array $transactionInfo, Request $request): JsonResponse
    {
        $package = Package::where('id', $packageId)
            ->where('is_active', true)
            ->where('is_ios_active', true)
            ->where('apple_product_id', $transactionInfo['product_id'] ?? null)
            ->first();

        if (! $package) {
            return $this->ApiResponseFormatted(
                404,
                [
                    'success' => false,
                    'message' => trans('api.package_not_available_for_ios'),
                ],
                trans('api.package_not_available_for_ios'),
                request: $request
            );
        }

        $metadata = array_filter([
            'transaction_info' => $transactionInfo['payload'] ?? null,
            'server_transaction' => $transactionInfo['server_payload'] ?? null,
            'renewal_info' => $transactionInfo['renewal_payload'] ?? null,
            'original_transaction_id' => $transactionInfo['original_transaction_id'] ?? null,
            'credits_added' => $package->credits,
            'environment' => $transactionInfo['environment'] ?? 'unknown',
        ]);

        $result = DB::transaction(function () use ($user, $package, $transactionId, $metadata) {
            $purchase = Purchase::create([
                'payable_type' => get_class($user),
                'payable_id' => $user->id,
                'purchasable_type' => get_class($package),
                'purchasable_id' => $package->id,
                'amount' => $package->getIosFinalPrice(),
                'status' => 1,
                'is_form_wallet' => false,
                'transaction_id' => $transactionId,
                'payment_method' => 'apple_iap',
                'currency' => $package->currency?->code ?? 'SAR',
                'metadata' => $metadata,
            ]);

            $walletDescription = trans('api.ios_package_purchase_wallet_note', [
                'name' => $package->getTranslation('name', app()->getLocale()) ?? $package->name,
            ]);

            $transaction = charge($package->credits)
                ->to($user)
                ->overCharge()
                ->meta([
                    'description' => $walletDescription,
                    'purchase_id' => $purchase->id,
                    'apple_transaction_id' => $transactionId,
                    'data' => $walletDescription,
                    'package' => $package->toArray(),
                ])
                ->commit();

            return [
                'purchase' => $purchase,
                'wallet_transaction' => $transaction,
            ];
        });

        $purchase = $result['purchase'];

        $data = [
            'purchase_id' => $purchase->id,
            'credits_added' => $package->credits,
            'environment' => $transactionInfo['environment'] ?? 'unknown',
        ];

        return $this->ApiResponseFormatted(
            200,
            [
                'success' => true,
                'message' => trans('api.package_purchased_successfully'),
                'data' => $data,
            ],
            trans('api.package_purchased_successfully'),
            request: $request
        );
    }

    /**
     * Process subscription purchase
     */
    private function processSubscriptionPurchase(Seller $user, PricingPlan $plan, string $transactionId, array $transactionInfo, Request $request, array $selectionData = []): JsonResponse
    {
        $existingSubscription = $user->subscriptions()
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->first();

        if ($existingSubscription) {
            $existingSubscription->update([
                'status' => SubscriptionStatus::CANCELLED,
                'canceled_at' => Carbon::now(),
            ]);
        }

        $expiresDateMs = $transactionInfo['expires_date_ms'] ?? null;
        $expiresAt = $expiresDateMs
            ? Carbon::createFromTimestampMs($expiresDateMs, 'UTC')->setTimezone(config('app.timezone'))
            : Carbon::now()->addMonths($plan->billing_cycles === 1 ? 12 : 1);

        $paymentDetails = array_filter([
            'transaction_info' => $transactionInfo['payload'] ?? null,
            'server_transaction' => $transactionInfo['server_payload'] ?? null,
            'renewal_info' => $transactionInfo['renewal_payload'] ?? null,
            'environment' => $transactionInfo['environment'] ?? 'unknown',
            'apple_transaction_id' => $transactionId,
            'original_transaction_id' => $transactionInfo['original_transaction_id'] ?? null,
        ]);

        $metadata = array_merge($paymentDetails, array_filter([
            'selection' => empty($selectionData) ? null : $selectionData,
            'plan_snapshot' => [
                'premium_type' => $plan->premium_type?->value,
                'credit_type' => $plan->credit_type?->value,
                'premium_items_limit' => $plan->premium_items_limit,
                'credit_items_limit' => $plan->credit_items_limit,
            ],
        ]));

        $result = DB::transaction(function () use ($user, $plan, $paymentDetails, $metadata, $transactionInfo, $transactionId, $expiresAt, $selectionData) {
            $subscription = Subscription::create([
                'seller_id' => $user->id,
                'price_plan_id' => $plan->id,
                'country_id' => getCountryId(),
                'status' => SubscriptionStatus::ACTIVE,
                'subscribe_at' => Carbon::now(),
                'ends_at' => $expiresAt,
                'renew_at' => $expiresAt,
                'next_renew_at' => $expiresAt,
                'is_auto_renew' => true,
                'is_yearly' => (int) $plan->billing_cycles === 1,
                'is_monthly' => (int) $plan->billing_cycles !== 1,
                'total_price' => $plan->getIosFinalPrice(),
                'is_premium' => $plan->is_premium,
                'is_in_credit' => $plan->is_in_credit ?? false,
                'premium_type' => $plan->premium_type,
                'credit_type' => $plan->credit_type,
                'premium_items_limit' => $plan->premium_items_limit,
                'credit_items_limit' => $plan->credit_items_limit,
                'payment_status' => true,
                'payment_details' => $paymentDetails,
                'metadata' => $metadata,
            ]);

            if (! empty($selectionData)) {
                $this->attachSubscriptionItems($subscription, $selectionData);
            }

            $purchase = Purchase::create([
                'payable_type' => get_class($user),
                'payable_id' => $user->id,
                'purchasable_type' => get_class($plan),
                'purchasable_id' => $plan->id,
                'amount' => $plan->getIosFinalPrice(),
                'status' => 1,
                'is_form_wallet' => false,
                'transaction_id' => $transactionId,
                'payment_method' => 'apple_iap',
                'metadata' => array_merge($metadata, [
                    'subscription_id' => $subscription->id,
                ]),
                'currency' => $plan->currency?->code ?? 'SAR',
            ]);

            return [
                'subscription' => $subscription,
                'purchase' => $purchase,
            ];
        });

        $subscription = $result['subscription'];
        $purchase = $result['purchase'];

        $data = [
            'subscription_id' => $subscription->id,
            'purchase_id' => $purchase->id,
            'expires_at' => $expiresAt?->toISOString(),
            'plan_name' => $plan->getTranslations('name'),
            'environment' => $transactionInfo['environment'] ?? 'unknown',
            'selection' => empty($selectionData) ? null : $selectionData,
        ];

        return $this->ApiResponseFormatted(
            200,
            [
                'success' => true,
                'message' => trans('api.subscription_activated_successfully'),
                'data' => $data,
            ],
            trans('api.subscription_activated_successfully'),
            request: $request
        );
    }

    private function findPlanForPurchase(Seller $seller, int $planId, string $appleProductId): ?PricingPlan
    {
        return PricingPlan::query()
            ->where('id', $planId)
            ->where('is_active', true)
            ->where('is_ios_active', true)
            ->where('apple_product_id', $appleProductId)
            ->when($seller->country_id, function ($query, $countryId) {
                $query->where(function ($q) use ($countryId) {
                    $q->whereNull('country_id')
                        ->orWhere('country_id', $countryId);
                });
            })
            ->with(['currency'])
            ->first();
    }

    private function formatPackageForResponse(Package $package): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $package->id,
            'type' => 'package',
            'apple_product_id' => $package->apple_product_id,
            'name' => $package->getTranslation('name', $locale) ?? $package->name,
            'description' => $package->getTranslation('description', $locale) ?? $package->description,
            'credits' => $package->credits,
            'price' => $package->getIosFinalPrice(),
            'original_price' => $package->ios_price ?? $package->price,
            'currency' => 'SAR',
            'local_currency' => $package->currency?->code,
            'local_price' => $package->price,
            'is_best_value' => (bool) $package->is_best_value,
        ];
    }

    private function formatSubscriptionForResponse(PricingPlan $plan, Seller $seller): array
    {
        $locale = app()->getLocale();

        $data = [
            'id' => $plan->id,
            'type' => 'subscription',
            'apple_product_id' => $plan->apple_product_id,
            'name' => $plan->getTranslation('name', $locale) ?? $plan->name,
            'description' => $plan->getTranslation('description', $locale) ?? $plan->description,
            'price' => $plan->getIosFinalPrice(),
            'original_price' => $plan->ios_price ?? $plan->month_price,
            'currency' => 'SAR',
            'local_currency' => $plan->currency?->code,
            'local_price' => $plan->month_price,
            'billing_cycle' => $plan->billing_cycles,
            'is_premium' => (bool) $plan->is_premium,
            'premium_type' => $plan->premium_type?->value,
            'premium_items_limit' => $plan->premium_items_limit,
            'is_in_credit' => (bool) $plan->is_in_credit,
            'credit_type' => $plan->credit_type?->value,
            'credit_items_limit' => $plan->credit_items_limit,
            'is_best_value' => (bool) $plan->is_best_value,
            'features' => $this->formatPlanFeatures($plan),
            'tag' => $plan->tag,
        ];

        $selectionConfig = $this->buildPlanSelectionData($plan, $seller);

        if (! empty($selectionConfig['premium'])) {
            $data['premium_configuration'] = $selectionConfig['premium'];
        }

        if (! empty($selectionConfig['credit'])) {
            $data['credit_configuration'] = $selectionConfig['credit'];
        }

        return $data;
    }

    private function formatPlanFeatures(PricingPlan $plan): array
    {
        $features = $plan->features ?? [];

        if (! is_array($features)) {
            return [];
        }

        $locale = app()->getLocale();

        return collect($features)
            ->map(function ($feature) use ($locale) {
                if (is_array($feature)) {
                    return $feature[$locale] ?? reset($feature);
                }

                return $feature;
            })
            ->filter()
            ->values()
            ->all();
    }

    private function buildPlanSelectionData(PricingPlan $plan, Seller $seller): array
    {
        $config = [];

        if ($plan->is_premium && $plan->premium_type) {
            $premium = $this->makeSelectionConfiguration($plan->premium_type, $plan->premium_items_limit, SubscriptionPlanType::PREMIUM, $seller);

            if ($premium !== null) {
                $config['premium'] = $premium;
            }
        }

        if ($plan->is_in_credit && $plan->credit_type) {
            $credit = $this->makeSelectionConfiguration($plan->credit_type, $plan->credit_items_limit, SubscriptionPlanType::CREDIT, $seller);

            if ($credit !== null) {
                $config['credit'] = $credit;
            }
        }

        return $config;
    }

    private function makeSelectionConfiguration(PremiumType|CreditType $type, ?int $itemsLimit, SubscriptionPlanType $planType, Seller $seller): ?array
    {
        $column = $type->getColumnName();
        $options = $this->fetchSelectionOptions($type, $planType, $seller);

        return [
            'enabled' => true,
            'type' => $type->value,
            'type_label' => method_exists($type, 'getSingleLabel') ? $type->getSingleLabel() : $type->getLabel(),
            'column' => $column,
            'items_limit' => $itemsLimit,
            'is_unlimited' => $itemsLimit === -1,
            'requires_selection' => $itemsLimit !== -1,
            'available_count' => count($options),
            'options' => $options,
        ];
    }

    private function fetchSelectionOptions(PremiumType|CreditType $type, SubscriptionPlanType $planType, Seller $seller): array
    {
        $column = $type->getColumnName();
        $assignedIds = $this->getAssignedSelectionIds($seller, $planType, $column);

        return match ($column) {
            'service_id' => $this->formatServiceOptions($seller, $assignedIds),
            'main_category_id' => $this->formatCategoryOptions(true, $assignedIds),
            'sub_category_id' => $this->formatCategoryOptions(false, $assignedIds),
            default => [],
        };
    }

    private function getAssignedSelectionIds(Seller $seller, SubscriptionPlanType $planType, string $column, array $candidateIds = []): array
    {
        $query = SubscriptionItem::query()
            ->where('type', $planType->value)
            ->whereNotNull($column)
            ->whereHas('subscription', function ($query) use ($seller) {
                $query->where('seller_id', $seller->id)
                    ->where('status', SubscriptionStatus::ACTIVE->value)
                    ->whereNull('canceled_at');
            });

        if (! empty($candidateIds)) {
            $query->whereIn($column, $candidateIds);
        }

        return $query->pluck($column)->filter()->unique()->values()->all();
    }

    private function formatCategoryOptions(bool $isMainCategory, array $excludeIds): array
    {
        $locale = app()->getLocale();

        $query = Category::query()
            ->withoutTrashed()
            ->whereNotIn('id', $excludeIds)
            ->orderBy('name->'.$locale);

        if ($isMainCategory) {
            $query->whereNull('parent_id');
        } else {
            $query->whereNotNull('parent_id')->with('parent');
        }

        return $query->get()
            ->map(function (Category $category) use ($isMainCategory, $locale) {
                return [
                    'id' => $category->id,
                    'name' => $category->getTranslation('name', $locale) ?? $category->name,
                    'translations' => $category->getTranslations('name'),
                    'meta' => array_filter([
                        'type' => $isMainCategory ? 'main_category' : 'sub_category',
                        'parent_id' => $category->parent_id,
                        'parent_name' => $category->parent?->getTranslation('name', $locale),
                    ]),
                ];
            })
            ->values()
            ->all();
    }

    private function formatServiceOptions(Seller $seller, array $excludeIds): array
    {
        $locale = app()->getLocale();

        return Service::query()
            ->withoutGlobalScope(TenantScope::class)
            ->withoutTrashed()
            ->whereNotIn('id', $excludeIds)
            ->when($seller->country_id, fn ($query, $countryId) => $query->where('country_id', $countryId))
            ->with(['category.parent'])
            ->orderBy('name->'.$locale)
            ->get()
            ->map(function (Service $service) use ($locale) {
                $subCategory = $service->category;
                $mainCategory = $subCategory?->parent;

                return [
                    'id' => $service->id,
                    'name' => $service->getTranslation('name', $locale) ?? $service->name,
                    'translations' => $service->getTranslations('name'),
                    'meta' => array_filter([
                        'type' => 'service',
                        'sub_category_id' => $subCategory?->id,
                        'sub_category_name' => $subCategory?->getTranslation('name', $locale),
                        'main_category_id' => $mainCategory?->id,
                        'main_category_name' => $mainCategory?->getTranslation('name', $locale),
                    ]),
                ];
            })
            ->values()
            ->all();
    }

    private function prepareSubscriptionSelections(PricingPlan $plan, Request $request, Seller $seller): array
    {
        $rules = [
            'premium' => ['nullable', 'array'],
            'credit' => ['nullable', 'array'],
        ];

        if ($plan->is_premium && $plan->premium_type) {
            $column = $plan->premium_type->getColumnName();
            $rules['premium'] = [$plan->premium_items_limit !== -1 ? 'required' : 'nullable', 'array:'.$column];
            $rules["premium.$column"] = ['nullable', 'array'];
            $rules["premium.$column.*"] = [
                'integer',
                'distinct',
                $this->makeExistsRuleForColumn($column),
            ];
        }

        if ($plan->is_in_credit && $plan->credit_type) {
            $column = $plan->credit_type->getColumnName();
            $rules['credit'] = [$plan->credit_items_limit !== -1 ? 'required' : 'nullable', 'array:'.$column];
            $rules["credit.$column"] = ['nullable', 'array'];
            $rules["credit.$column.*"] = [
                'integer',
                'distinct',
                $this->makeExistsRuleForColumn($column),
            ];
        }

        $validated = Validator::make($request->all(), $rules)->validate();

        $selections = [];

        if ($plan->is_premium && $plan->premium_type) {
            $selection = $this->resolveSelectionInput(
                $validated['premium'] ?? [],
                $plan->premium_type,
                $plan->premium_items_limit,
                $seller,
                SubscriptionPlanType::PREMIUM,
                'premium'
            );

            if (! empty($selection)) {
                $selections[SubscriptionPlanType::PREMIUM->value] = $selection;
            }
        }

        if ($plan->is_in_credit && $plan->credit_type) {
            $selection = $this->resolveSelectionInput(
                $validated['credit'] ?? [],
                $plan->credit_type,
                $plan->credit_items_limit,
                $seller,
                SubscriptionPlanType::CREDIT,
                'credit'
            );

            if (! empty($selection)) {
                $selections[SubscriptionPlanType::CREDIT->value] = $selection;
            }
        }

        return $selections;
    }

    private function resolveSelectionInput(array $input, PremiumType|CreditType $type, ?int $itemsLimit, Seller $seller, SubscriptionPlanType $planType, string $inputKey): array
    {
        $column = $type->getColumnName();
        $ids = array_map('intval', $input[$column] ?? []);
        $ids = array_values(array_unique(array_filter($ids)));

        $requiresSelection = $itemsLimit !== -1;

        if ($requiresSelection && empty($ids)) {
            $message = trans('api.subscription_selection_required');

            if ($message === 'api.subscription_selection_required') {
                $message = __('You must select at least one option to continue.');
            }

            throw ValidationException::withMessages([
                "$inputKey.$column" => [$message],
            ]);
        }

        if ($itemsLimit !== null && $itemsLimit > 0 && count($ids) > $itemsLimit) {
            $message = trans_choice('api.subscription_selection_limit', $itemsLimit, ['limit' => $itemsLimit]);

            if ($message === 'api.subscription_selection_limit') {
                $message = __('You can select up to :limit items for this subscription.', ['limit' => $itemsLimit]);
            }

            throw ValidationException::withMessages([
                "$inputKey.$column" => [$message],
            ]);
        }

        if (empty($ids)) {
            return [];
        }

        $conflicts = $this->getAssignedSelectionIds($seller, $planType, $column, $ids);

        if (! empty($conflicts)) {
            $message = trans('api.subscription_selection_conflict');

            if ($message === 'api.subscription_selection_conflict') {
                $message = __('One or more of the selected items are already part of another active subscription.');
            }

            throw ValidationException::withMessages([
                "$inputKey.$column" => [$message],
            ]);
        }

        return [$column => $ids];
    }

    private function makeExistsRuleForColumn(string $column): Rule
    {
        return match ($column) {
            'service_id' => Rule::exists('services', 'id')->whereNull('deleted_at'),
            'main_category_id' => Rule::exists('categories', 'id')->whereNull('deleted_at')->whereNull('parent_id'),
            'sub_category_id' => Rule::exists('categories', 'id')->whereNull('deleted_at')->whereNotNull('parent_id'),
            default => Rule::exists('categories', 'id')->whereNull('deleted_at'),
        };
    }

    private function attachSubscriptionItems(Subscription $subscription, array $selectionData): void
    {
        $items = [];

        foreach ($selectionData as $type => $selection) {
            if (! in_array($type, [SubscriptionPlanType::PREMIUM->value, SubscriptionPlanType::CREDIT->value], true)) {
                continue;
            }

            foreach ($selection as $column => $ids) {
                foreach ($ids as $id) {
                    $items[] = [
                        'subscription_id' => $subscription->id,
                        $column => $id,
                        'quantity' => 1,
                        'type' => $type,
                    ];
                }
            }
        }

        if (! empty($items)) {
            $subscription->items()->createMany($items);
        }
    }

    /**
     * Handle App Store Server Notifications V2
     */
    public function handleServerNotification(Request $request): JsonResponse
    {
        try {
            Log::channel('IOS')->info('App Store Server Notification Received', $request->all());

            $signedPayload = $request->input('signedPayload');

            if (! $signedPayload) {
                return response()->json(['status' => 'error', 'message' => 'Missing signed payload'], 400);
            }

            $processed = $this->decodeNotificationPayload($signedPayload);

            if ($processed === null) {
                return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
            }

            $notificationPayload = $processed['payload'];
            $transactionPayload = $processed['transaction'];
            $renewalPayload = $processed['renewal'];

            $notificationUuid = $notificationPayload['notificationUUID'] ?? null;

            if ($notificationUuid && DB::table('webhook_calls')
                ->where('name', 'apple_server_notification')
                ->where('url', $notificationUuid)
                ->exists()) {
                Log::channel('IOS')->info('Duplicate App Store notification ignored', [
                    'notification_uuid' => $notificationUuid,
                ]);

                return response()->json(['status' => 'success'], 200);
            }

            DB::table('webhook_calls')->insert([
                'name' => 'apple_server_notification',
                'url' => $notificationUuid ?? $request->url(),
                'payload' => json_encode([
                    'signedPayload' => $signedPayload,
                    'notification' => $notificationPayload,
                    'transaction' => $transactionPayload,
                    'renewal' => $renewalPayload,
                ]),
                'headers' => json_encode($request->headers->all()),
                'exception' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $notificationType = $notificationPayload['notificationType'] ?? null;
            $subtype = $notificationPayload['subtype'] ?? null;

            $bundleId = $transactionPayload['bundleId']
                ?? $transactionPayload['bundleIdentifier']
                ?? ($notificationPayload['data']['bundleId'] ?? null);

            if ($bundleId !== null && $bundleId !== $this->appleInAppPurchaseService->getBundleIdentifier()) {
                Log::channel('IOS')->warning('Ignoring notification for mismatched bundle identifier', [
                    'bundle_id' => $bundleId,
                ]);

                return response()->json(['status' => 'ignored'], 200);
            }

            Log::channel('IOS')->info('Notification Details', [
                'type' => $notificationType,
                'subtype' => $subtype,
                'transaction_id' => $transactionPayload['transactionId'] ?? null,
                'original_transaction_id' => $transactionPayload['originalTransactionId'] ?? null,
            ]);

            switch ($notificationType) {
                case 'SUBSCRIBED':
                    $this->handleSubscriptionStarted($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'DID_CHANGE_RENEWAL_STATUS':
                    $this->handleRenewalStatusChange($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'DID_RENEW':
                    $this->handleSubscriptionRenewal($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'EXPIRED':
                    $this->handleSubscriptionExpired($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'DID_FAIL_TO_RENEW':
                    $this->handleRenewalFailure($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'BILLING_RECOVERY':
                    $this->handleBillingRecovery($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'BILLING_RETRY':
                    $this->handleBillingRetry($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'GRACE_PERIOD_EXPIRED':
                    $this->handleGracePeriodExpired($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'RENEWAL_EXTENDED':
                    $this->handleRenewalExtended($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'OFFER_REDEEMED':
                    $this->handleOfferRedeemed($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'REFUND':
                    $this->handleRefund($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'REFUND_DECLINED':
                    $this->handleRefundDeclined($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'CONSUMPTION_REQUEST':
                    $this->handleConsumptionRequest($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'DID_CHANGE_RENEWAL_PREF':
                    $this->handleRenewalPreferenceChange($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'DID_TRANSFER':
                    $this->handleSubscriptionTransfer($notificationPayload, $transactionPayload, $renewalPayload);
                    break;
                case 'TEST':
                    Log::channel('IOS')->info('Test notification received');
                    break;
                default:
                    Log::channel('IOS')->info('Unhandled notification type', ['type' => $notificationType]);
                    break;
            }

            return response()->json(['status' => 'success'], 200);
        } catch (\Throwable $throwable) {
            Log::channel('IOS')->error('App Store Server Notification Error', [
                'message' => $throwable->getMessage(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Processing failed'], 500);
        }
    }

    private function decodeNotificationPayload(string $signedPayload): ?array
    {
        try {
            $decoded = $this->appleInAppPurchaseService->decodeSignedPayload($signedPayload);

            if (! $decoded || ! isset($decoded['payload']) || ! is_array($decoded['payload'])) {
                return null;
            }

            $payload = $decoded['payload'];
            $data = $payload['data'] ?? [];

            $transactionPayload = [];
            if (! empty($data['signedTransactionInfo'])) {
                $transactionDecoded = $this->appleInAppPurchaseService->decodeSignedPayload($data['signedTransactionInfo']);
                $transactionPayload = $transactionDecoded['payload'] ?? [];
            } elseif (! empty($data['transactionInfo'])) {
                $transactionPayload = $data['transactionInfo'];
            }

            $renewalPayload = [];
            if (! empty($data['signedRenewalInfo'])) {
                $renewalDecoded = $this->appleInAppPurchaseService->decodeSignedPayload($data['signedRenewalInfo']);
                $renewalPayload = $renewalDecoded['payload'] ?? [];
            } elseif (! empty($data['renewalInfo'])) {
                $renewalPayload = $data['renewalInfo'];
            }

            return [
                'payload' => $payload,
                'transaction' => $transactionPayload,
                'renewal' => $renewalPayload,
            ];
        } catch (\Throwable $throwable) {
            Log::channel('IOS')->error('Failed to decode notification payload', [
                'message' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    private function handleSubscriptionStarted(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing subscription started', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
        ]);

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if (! $subscription) {
            Log::channel('IOS')->warning('Subscription not found for SUBSCRIBED notification', [
                'transaction_id' => $transactionPayload['transactionId'] ?? null,
            ]);

            return;
        }

        $expiresAt = $this->carbonFromMillis(
            $transactionPayload['expiresDateMs']
            ?? $transactionPayload['expiresDate']
            ?? $renewalPayload['expiresDateMs']
            ?? $renewalPayload['expiresDate']
            ?? null
        );

        $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);

        $subscription->status = SubscriptionStatus::ACTIVE;
        $subscription->canceled_at = null;

        if ($expiresAt) {
            $subscription->ends_at = $expiresAt;
            $subscription->renew_at = $expiresAt;
            $subscription->next_renew_at = $expiresAt;
        }

        $subscription->save();
    }

    private function handleRenewalStatusChange(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing renewal status change', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
            'subtype' => $notificationPayload['subtype'] ?? null,
        ]);

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if (! $subscription) {
            Log::channel('IOS')->warning('Subscription not found for renewal status change', [
                'transaction_id' => $transactionPayload['transactionId'] ?? null,
            ]);

            return;
        }

        $subtype = $notificationPayload['subtype'] ?? null;

        if ($subtype === 'AUTO_RENEW_ENABLED') {
            $subscription->is_auto_renew = true;
            $subscription->canceled_at = null;
            if ($subscription->status === SubscriptionStatus::CANCELLED) {
                $subscription->status = SubscriptionStatus::ACTIVE;
            }
        } elseif ($subtype === 'AUTO_RENEW_DISABLED') {
            $subscription->is_auto_renew = false;
            $subscription->canceled_at = Carbon::now();
            $subscription->status = SubscriptionStatus::CANCELLED;
        }

        $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);

        $subscription->save();
    }

    private function handleSubscriptionRenewal(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing subscription renewal', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
        ]);

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if (! $subscription) {
            Log::channel('IOS')->warning('Subscription not found for DID_RENEW notification', [
                'transaction_id' => $transactionPayload['transactionId'] ?? null,
            ]);

            return;
        }

        $expiresAt = $this->carbonFromMillis(
            $transactionPayload['expiresDateMs']
            ?? $transactionPayload['expiresDate']
            ?? $renewalPayload['renewalDate']
            ?? $renewalPayload['expiresDateMs']
            ?? null
        );

        $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);

        $subscription->status = SubscriptionStatus::ACTIVE;
        $subscription->canceled_at = null;

        if ($expiresAt) {
            $subscription->ends_at = $expiresAt;
            $subscription->renew_at = $expiresAt;
            $subscription->next_renew_at = $expiresAt;
        }

        $subscription->save();
    }

    private function handleSubscriptionExpired(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing subscription expiration', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
        ]);

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if (! $subscription) {
            Log::channel('IOS')->warning('Subscription not found for EXPIRED notification', [
                'transaction_id' => $transactionPayload['transactionId'] ?? null,
            ]);

            return;
        }

        $expiresAt = $this->carbonFromMillis(
            $transactionPayload['expiresDateMs']
            ?? $transactionPayload['expiresDate']
            ?? $renewalPayload['expiresDateMs']
            ?? $renewalPayload['expiresDate']
            ?? null
        );

        $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);

        $subscription->status = SubscriptionStatus::EXPIRED;
        $subscription->canceled_at = $subscription->canceled_at ?? Carbon::now();

        if ($expiresAt) {
            $subscription->ends_at = $expiresAt;
        }

        $subscription->save();
    }

    private function handleRenewalFailure(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing renewal failure', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
            'reason' => $renewalPayload['expirationIntent'] ?? null,
        ]);

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if (! $subscription) {
            Log::channel('IOS')->warning('Subscription not found for DID_FAIL_TO_RENEW notification', [
                'transaction_id' => $transactionPayload['transactionId'] ?? null,
            ]);

            return;
        }

        $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);

        $subscription->status = SubscriptionStatus::CANCELLED;
        $subscription->canceled_at = Carbon::now();

        $metadata = $subscription->metadata ?? [];
        $metadata['renewal_failure_reason'] = $renewalPayload['expirationIntent'] ?? $notificationPayload['subtype'] ?? 'unknown';
        $subscription->metadata = $metadata;

        $subscription->save();
    }

    private function handleRefund(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing refund', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
        ]);

        $transactionId = $transactionPayload['transactionId'] ?? null;

        if ($transactionId) {
            $purchase = $this->findPurchaseByTransaction($transactionId);

            if ($purchase) {
                $purchase->status = 0;
                $purchase->metadata = array_merge($purchase->metadata ?? [], [
                    'apple_refund_notification' => $notificationPayload,
                ]);
                $purchase->save();

                if ($purchase->purchasable_type === Package::class) {
                    $package = $purchase->purchasable;
                    $user = $purchase->payable;

                    if ($package && $user) {
                        $description = trans('api.ios_package_purchase_refund_note', [
                            'name' => $package->getTranslation('name', app()->getLocale()) ?? $package->name,
                        ]);

                        withdraw($package->credits)
                            ->from($user)
                            ->overCharge()
                            ->meta([
                                'description' => $description,
                                'purchase_id' => $purchase->id,
                                'apple_transaction_id' => $transactionId,
                                'data' => $description,
                                'refund' => true,
                            ])
                            ->commit();
                    }
                }
            } else {
                Log::channel('IOS')->warning('Purchase not found for refund notification', [
                    'transaction_id' => $transactionId,
                ]);
            }
        }

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if ($subscription) {
            $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);

            $subscription->status = SubscriptionStatus::REFUND;
            $subscription->canceled_at = Carbon::now();
            $subscription->save();
        } else {
            Log::channel('IOS')->warning('Subscription not found for refund notification', [
                'transaction_id' => $transactionPayload['transactionId'] ?? null,
            ]);
        }
    }

    private function handleRefundDeclined(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing refund declined', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
        ]);

        $transactionId = $transactionPayload['transactionId'] ?? null;

        if (! $transactionId) {
            return;
        }

        $purchase = $this->findPurchaseByTransaction($transactionId);

        if ($purchase) {
            $metadata = $purchase->metadata ?? [];
            $metadata['apple_refund_declined'] = [
                'received_at' => now()->toIso8601String(),
                'notification' => [
                    'type' => $notificationPayload['notificationType'] ?? null,
                    'subtype' => $notificationPayload['subtype'] ?? null,
                ],
            ];
            $purchase->metadata = $metadata;
            $purchase->save();
        }

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if ($subscription) {
            $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);
            $metadata = $subscription->metadata ?? [];
            $metadata['refund_declined_at'] = now()->toIso8601String();
            $subscription->metadata = $metadata;
            $subscription->save();
        }
    }

    private function handleBillingRecovery(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing billing recovery', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
        ]);

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if (! $subscription) {
            Log::channel('IOS')->warning('Subscription not found for BILLING_RECOVERY notification', [
                'transaction_id' => $transactionPayload['transactionId'] ?? null,
            ]);

            return;
        }

        $expiresAt = $this->resolveExpiryFromPayload($transactionPayload, $renewalPayload);

        $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);

        $subscription->status = SubscriptionStatus::ACTIVE;
        $subscription->canceled_at = null;

        if ($expiresAt) {
            $subscription->ends_at = $expiresAt;
            $subscription->renew_at = $expiresAt;
            $subscription->next_renew_at = $expiresAt;
        }

        $metadata = $subscription->metadata ?? [];
        $metadata['billing_recovery_at'] = now()->toIso8601String();
        $subscription->metadata = $metadata;

        $subscription->save();
    }

    private function handleBillingRetry(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing billing retry', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
        ]);

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if (! $subscription) {
            Log::channel('IOS')->warning('Subscription not found for BILLING_RETRY notification', [
                'transaction_id' => $transactionPayload['transactionId'] ?? null,
            ]);

            return;
        }

        $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);

        $metadata = $subscription->metadata ?? [];
        $metadata['billing_retry'] = array_filter([
            'received_at' => now()->toIso8601String(),
            'retry_count' => $renewalPayload['retryCount'] ?? null,
            'grace_period_expires' => $renewalPayload['gracePeriodExpiresDate']
                ?? $renewalPayload['gracePeriodExpiresDateMs']
                ?? null,
        ]);
        $subscription->metadata = $metadata;

        $gracePeriod = $this->carbonFromMillis(
            $renewalPayload['gracePeriodExpiresDate']
            ?? $renewalPayload['gracePeriodExpiresDateMs']
            ?? null
        );

        if ($gracePeriod) {
            $subscription->next_renew_at = $gracePeriod;
        }

        $subscription->save();
    }

    private function handleGracePeriodExpired(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing grace period expired', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
        ]);

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if (! $subscription) {
            Log::channel('IOS')->warning('Subscription not found for GRACE_PERIOD_EXPIRED notification', [
                'transaction_id' => $transactionPayload['transactionId'] ?? null,
            ]);

            return;
        }

        $expiresAt = $this->resolveExpiryFromPayload($transactionPayload, $renewalPayload);

        $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);

        $subscription->status = SubscriptionStatus::CANCELLED;
        $subscription->canceled_at = Carbon::now();

        if ($expiresAt) {
            $subscription->ends_at = $expiresAt;
        }

        $metadata = $subscription->metadata ?? [];
        $metadata['grace_period_expired_at'] = now()->toIso8601String();
        $subscription->metadata = $metadata;

        $subscription->save();
    }

    private function handleRenewalExtended(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing renewal extended', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
        ]);

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if (! $subscription) {
            Log::channel('IOS')->warning('Subscription not found for RENEWAL_EXTENDED notification', [
                'transaction_id' => $transactionPayload['transactionId'] ?? null,
            ]);

            return;
        }

        $expiresAt = $this->resolveExpiryFromPayload($transactionPayload, $renewalPayload);

        $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);

        if ($expiresAt) {
            $subscription->ends_at = $expiresAt;
            $subscription->renew_at = $expiresAt;
            $subscription->next_renew_at = $expiresAt;
        }

        $metadata = $subscription->metadata ?? [];
        $metadata['renewal_extended_at'] = now()->toIso8601String();
        $subscription->metadata = $metadata;

        $subscription->save();
    }

    private function handleOfferRedeemed(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing offer redeemed', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
        ]);

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if (! $subscription) {
            Log::channel('IOS')->warning('Subscription not found for OFFER_REDEEMED notification', [
                'transaction_id' => $transactionPayload['transactionId'] ?? null,
            ]);

            return;
        }

        $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);

        $metadata = $subscription->metadata ?? [];
        $metadata['offer_redeemed'] = array_filter([
            'redeemed_at' => now()->toIso8601String(),
            'offer_identifier' => $renewalPayload['offerIdentifier'] ?? null,
            'offer_type' => $renewalPayload['offerType'] ?? null,
        ]);
        $subscription->metadata = $metadata;

        $subscription->save();
    }

    private function handleConsumptionRequest(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing consumption request', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
        ]);

        $transactionId = $transactionPayload['transactionId'] ?? null;

        if (! $transactionId) {
            return;
        }

        $purchase = $this->findPurchaseByTransaction($transactionId);

        if ($purchase) {
            $metadata = $purchase->metadata ?? [];
            $metadata['consumption_request'] = array_filter([
                'received_at' => now()->toIso8601String(),
                'request_identifier' => $notificationPayload['data']['consumptionRequest']['identifier'] ?? null,
                'request_reason' => $notificationPayload['data']['consumptionRequest']['reason'] ?? null,
            ]);
            $purchase->metadata = $metadata;
            $purchase->save();
        }

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if ($subscription) {
            $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);
            $subscription->save();
        }
    }

    private function handleRenewalPreferenceChange(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing renewal preference change', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
        ]);

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if (! $subscription) {
            Log::channel('IOS')->warning('Subscription not found for DID_CHANGE_RENEWAL_PREF notification', [
                'transaction_id' => $transactionPayload['transactionId'] ?? null,
            ]);

            return;
        }

        $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);

        if (! empty($renewalPayload['autoRenewProductId'])) {
            $newPlan = PricingPlan::where('apple_product_id', $renewalPayload['autoRenewProductId'])->first();

            if ($newPlan && $subscription->price_plan_id !== $newPlan->id) {
                $subscription->price_plan_id = $newPlan->id;
                $subscription->total_price = $newPlan->getIosFinalPrice();
            }
        }

        $metadata = $subscription->metadata ?? [];
        $metadata['renewal_preference'] = array_filter([
            'updated_at' => now()->toIso8601String(),
            'auto_renew_product_id' => $renewalPayload['autoRenewProductId'] ?? null,
        ]);
        $subscription->metadata = $metadata;

        $subscription->save();
    }

    private function handleSubscriptionTransfer(array $notificationPayload, array $transactionPayload, array $renewalPayload): void
    {
        Log::channel('IOS')->info('Processing subscription transfer', [
            'transaction_id' => $transactionPayload['transactionId'] ?? null,
        ]);

        $subscription = $this->findSubscriptionByTransaction($transactionPayload, $renewalPayload);

        if (! $subscription) {
            Log::channel('IOS')->warning('Subscription not found for DID_TRANSFER notification', [
                'transaction_id' => $transactionPayload['transactionId'] ?? null,
            ]);

            return;
        }

        $this->syncSubscriptionSnapshot($subscription, $transactionPayload, $renewalPayload, $notificationPayload);

        $subscription->status = SubscriptionStatus::CANCELLED;
        $subscription->canceled_at = Carbon::now();

        $metadata = $subscription->metadata ?? [];
        $metadata['transferred'] = array_filter([
            'transferred_at' => now()->toIso8601String(),
            'old_transaction' => $transactionPayload['transactionId'] ?? null,
        ]);
        $subscription->metadata = $metadata;

        $subscription->save();
    }

    private function resolveExpiryFromPayload(array $transactionPayload, array $renewalPayload): ?Carbon
    {
        return $this->carbonFromMillis(
            $transactionPayload['expiresDateMs']
            ?? $transactionPayload['expiresDate']
            ?? $renewalPayload['expiresDateMs']
            ?? $renewalPayload['expiresDate']
            ?? $renewalPayload['renewalDate']
            ?? null
        );
    }

    private function findSubscriptionByTransaction(array $transactionPayload, array $renewalPayload): ?Subscription
    {
        $transactionId = $transactionPayload['transactionId'] ?? null;
        $originalTransactionId = $transactionPayload['originalTransactionId'] ?? ($renewalPayload['originalTransactionId'] ?? null);

        if ($transactionId) {
            $subscription = Subscription::where('payment_details->apple_transaction_id', $transactionId)
                ->orWhere('metadata->apple_transaction_id', $transactionId)
                ->orWhere('metadata->latest_transaction->transactionId', $transactionId)
                ->first();

            if ($subscription) {
                return $subscription;
            }
        }

        if ($originalTransactionId) {
            return Subscription::where('payment_details->transaction_info->originalTransactionId', $originalTransactionId)
                ->orWhere('metadata->transaction_info->originalTransactionId', $originalTransactionId)
                ->orWhere('metadata->latest_transaction->originalTransactionId', $originalTransactionId)
                ->first();
        }

        return null;
    }

    private function findPurchaseByTransaction(string $transactionId): ?Purchase
    {
        return Purchase::where('transaction_id', $transactionId)
            ->orWhere('metadata->apple_transaction_id', $transactionId)
            ->orWhere('metadata->original_transaction_id', $transactionId)
            ->first();
    }

    private function syncSubscriptionSnapshot(Subscription $subscription, array $transactionPayload, array $renewalPayload, array $notificationPayload): void
    {
        $paymentDetails = $subscription->payment_details ?? [];
        $metadata = $subscription->metadata ?? [];

        if (! empty($transactionPayload)) {
            $paymentDetails['latest_transaction'] = $transactionPayload;
            $metadata['latest_transaction'] = $transactionPayload;

            if (isset($transactionPayload['transactionId'])) {
                $paymentDetails['apple_transaction_id'] = $transactionPayload['transactionId'];
            }

            if (isset($transactionPayload['originalTransactionId'])) {
                $paymentDetails['original_transaction_id'] = $transactionPayload['originalTransactionId'];
            }
        }

        if (! empty($renewalPayload)) {
            $paymentDetails['latest_renewal'] = $renewalPayload;
            $metadata['latest_renewal'] = $renewalPayload;

            if (isset($renewalPayload['autoRenewStatus'])) {
                $subscription->is_auto_renew = (int) $renewalPayload['autoRenewStatus'] === 1;
            }
        }

        $paymentDetails['latest_notification'] = $notificationPayload;
        $metadata['latest_notification'] = $notificationPayload;

        $subscription->payment_details = $paymentDetails;
        $subscription->metadata = $metadata;
    }

    private function carbonFromMillis(mixed $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $timestamp = (int) $value;

        if ($timestamp <= 0) {
            return null;
        }

        if ($timestamp > 9999999999) {
            return Carbon::createFromTimestampMs($timestamp, 'UTC')->setTimezone(config('app.timezone'));
        }

        return Carbon::createFromTimestamp($timestamp, 'UTC')->setTimezone(config('app.timezone'));
    }
}
