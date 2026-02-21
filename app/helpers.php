<?php

use App\Concerns\SubscribeFrom;
use App\Enums\QuestionType;
use App\Enums\Wallet\SubscriptionPlanType;
use App\Extensions\CustomPhoneInput;
use App\Models\Country;
use App\Models\CustomerAnswer;
use App\Models\Media;
use App\Models\Package;
use App\Models\Seller;
use App\Models\Service;
use App\Settings\GeneralSettings;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Hibit\GeoDetect;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use LaraZeus\Sky\SkyPlugin;
use O21\LaravelWallet\Models\Transaction;
use Symfony\Component\HttpFoundation\Response;
use Wamania\Snowball\StemmerFactory;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
if (!function_exists('priceWithVat')) {
    function priceWithVat(float $price): float
    {
        $vatPercentage =  (getCurrentTenant()?->vat_percentage ?? 0) / 100;
        return round( $price + ($price * $vatPercentage) ,2);
     }
}
if (!function_exists('getPhoneInput')) {
    function getPhoneInput($name='phone',$user=null,$record=null):CustomPhoneInput
    {
        $current_tenant = strtolower(getCountryCode());
       return CustomPhoneInput::make($name??'phone')
           ->required()
//            ->inputNumberFormat(PhoneInputNumberType::NATIONAL)
//            ->displayNumberFormat(PhoneInputNumberType::NATIONAL)
//            ->formatOnDisplay(true)
            ->validateFor(
                country: strtoupper($current_tenant), // default: 'AUTO'
            //  type: PhoneNumberType::MOBILE | PhoneNumberType::FIXED_LINE, // default: null
            //  lenient: true, // default: false
            )
            ->label(__('cv.phone_number'))
            ->initialCountry($current_tenant)
            ->defaultCountry($current_tenant)
            ->onlyCountries([$current_tenant])
            ->unique($user,ignorable:$record ,ignoreRecord: true)
            ->extraInputAttributes(['tabindex' => 1, 'class' => 'block w-full py-4 pr-20  placeholder:text-gray-400 sm:text-sm sm:leading-6']);

    }
}
if (!function_exists('customerTextAnswer')) {
    function customerTextAnswer(CustomerAnswer $customerAnswer): string
    {
        return match ($customerAnswer->question_type) {
            QuestionType::Text, QuestionType::TextArea,
            QuestionType::Number, QuestionType::Date,
            QuestionType::Location, QuestionType::DateRange => $customerAnswer->text_answer,
            QuestionType::SELECT , QuestionType::Checkbox => handleCustomAnswer($customerAnswer),
            QuestionType::PreciseDate => getPreciseDateString($customerAnswer->toArray()),

            QuestionType::Attachments => new HtmlString(($customerAnswer->voice_note != null ? __('columns.voice_note') .": ". voiceAnswer($customerAnswer)  : '') . "<br>"
                .($customerAnswer->attachment != null ? __('columns.attachments' ) . ": (" .count($customerAnswer->attachment) . ")" : '')),

            default =>  __('columns.na'),
        };

    }
}
if (!function_exists('voiceAnswer')) {
    function voiceAnswer(CustomerAnswer $record): string
    {
        if($record->voice_note_moderation != null){
            if(isset($record->voice_note_moderation['html'] , $record->voice_note_moderation['is_acceptable']) && !$record->voice_note_moderation['is_acceptable']){
                return new HtmlString($record->voice_note_moderation['html']);
            }elseif (isset($record->voice_note_text)){
                return $record->voice_note_text;
            }else{
                return __('columns.na');
            }
        }
        return '';
    }
}
if (!function_exists('getCountryCode')) {
    function getCountryCode(): string
    {
        $code = getCurrentTenant()?->code ?? 'SA';
        // Ensure it's a valid 2-letter country code
        if (strlen($code) === 2) {
            return strtoupper($code);
        }
        return 'SA'; // Default fallback
    }
}

if (!function_exists('handleCustomAnswer')) {
    function handleCustomAnswer(CustomerAnswer $answer): string
    {
        if ($answer->is_custom && filled($answer->custom_answer)) {
            return $answer->answer_label . ' (' . $answer->custom_answer . ')';
        } else {
            return $answer->answer_label;
        }
    }
}


if (!function_exists('getCurrencySample')) {
    function getCurrencySample(): ?string
    {
        return getCurrentTenant()?->currency?->symbol;
    }
}

if (!function_exists('normalizeArabic')) {

    function normalizeArabic($text)
    {
        $replacements = [
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ى' => 'ي',
            'ئ' => 'ي',
            'ؤ' => 'و',
            'ة' => 'ه',
        ];

        return strtr($text, $replacements);
    }
}

if (!function_exists('getArabicVariations')) {

    function getArabicVariations($text)
    {
        $variations = [
            'ا' => ['ا', 'أ', 'إ', 'آ'],
            'ي' => ['ي', 'ى', 'ئ'],
            'و' => ['و', 'ؤ'],
            'ه' => ['ه', 'ة'],
        ];

        $words = explode(' ', $text);
        $result = [];

        foreach ($words as $word) {
            $wordVariations = [''];
            for ($i = 0; $i < mb_strlen($word); $i++) {
                $char = mb_substr($word, $i, 1);
                $charVariations = $variations[$char] ?? [$char];
                $newVariations = [];
                foreach ($wordVariations as $prefix) {
                    foreach ($charVariations as $variation) {
                        $newVariations[] = $prefix . $variation;
                    }
                }
                $wordVariations = $newVariations;
            }
            $result = array_merge($result, $wordVariations);
        }

        return $result;
    }
}

if (!function_exists('generateVariations')) {

    function generateVariations($word)
    {
        return [
            Str::lower($word),
            Str::upper($word),
            Str::ucfirst(Str::lower($word)),
        ];
    }
}

if (!function_exists('searchWithVariations')) {

    function getWordVariations($searchTerm): array
    {
        $start = microtime(true);
        $words = explode(' ', $searchTerm);
        $normalizedWords = array_map('normalizeArabic', $words);
        $variations = [];

        foreach ($normalizedWords as $word) {
            $variations = array_merge($variations, generateVariations($word));
        }

        foreach ($normalizedWords as $word) {
            $variations = array_merge($variations, getArabicVariations($word));
        }

        return array_unique($variations);
    }
}

if (!function_exists('searchWithVariations')) {

    function searchWithVariations(Builder $query, $searchTerm, $column): Builder
    {

        return $query->where(function ($query) use ($searchTerm, $column) {
            foreach (getWordVariations($searchTerm) as $variation) {
                $query->orWhere($column, 'LIKE', "%{$variation}%");
            }
        });
    }
}

if (!function_exists('appUrl')) {
    function appUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        return (request())->getUriForPath('/storage/'.$path);
    }
}

if (!function_exists('mediaAppUrl')) {
    function mediaAppUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        return (request())->getUriForPath($path);
    }
}




if (!function_exists('getPreciseDateString')) {
    function getPreciseDateString($answer): ?string
    {
        return __('string.duration.string', [
            'date' => Carbon::parse($answer['text_answer'])->translatedFormat('d M Y'),
            'time' => $answer['time'],
            'duration' => getDurationString($answer)]);
    }
}

if (!function_exists('getDurationString')) {
    function getDurationString($answer): ?string
    {
        if ($answer['duration'] == 1) {
            return __('string.duration.type.' . $answer['duration_type'] . '.1');
        } elseif ($answer['duration'] == 2) {
            return __('string.duration.type.' . $answer['duration_type'] . '.2');

        } elseif ($answer['duration'] >= 3 && $answer['duration'] <= 10) {
            return __('string.duration.type.' . $answer['duration_type'] . '.3', [
                'count' => $answer['duration']
            ]);

        } else {
            return __('string.duration.type.' . $answer['duration_type'] . '.4', [
                'count' => $answer['duration']
            ]);
        }

    }
}

if (!function_exists('getMaxResponses')) {
    function getMaxResponses(): ?int
    {
        return app(GeneralSettings::class)->maximum_responses ?? 5;
    }
}

if (!function_exists('getLogo')) {
    function getLogo(): ?HtmlString
    {
        return new HtmlString(<<<HTML
                <a href="/">
                    <img src="/assets/logo/logo.png" alt="Logo" class="fi-logo flex">
                </a>
            HTML
        );
    }
}
if (!function_exists('getSmLogo')) {
    function getSmLogo(): ?HtmlString
    {
        if (app()->getLocale() == 'ar') {
            return new HtmlString(<<<HTML
                <a href="/" class=" flex sm:hidden justify-start" style="width: 45vw">
                    <img src="/assets/logo/logo_ar.png" alt="Logo" class="fi-logo flex">
                </a>
            HTML
            );
        }
        return new HtmlString(<<<HTML
                <a href="/" class=" flex sm:hidden justify-start" style="width: 45vw">
                    <img src="/assets/logo/logo_en.png" alt="Logo" class="fi-logo flex">
                </a>
            HTML
        );
    }
}


if(!function_exists('getArLogo')){
    function getArLogo(): ?HtmlString
    {
        return new HtmlString(<<<HTML
                <a href="/">
                    <img src="/assets/logo/logo_ar.png" alt="Logo" class="fi-logo flex">
                </a>
            HTML
        );
    }

}

if(!function_exists('getEnLogo')){
    function getEnLogo(): ?HtmlString
    {
        return new HtmlString(<<<HTML
                <a href="/">
                    <img src="/assets/logo/logo_en.png" alt="Logo" class="fi-logo flex">
                </a>
            HTML
        );
    }

}





if (!function_exists('addBullets')) {
    function addBullets($text): string
    {
        $lines = explode(PHP_EOL, $text);
        $bulletedLines = array_map(fn($line) => $line !== '' ? "• " . $line : '', $lines);
        return implode(PHP_EOL, $bulletedLines);
    }
}

if (!function_exists('chargeCreditBalance')) {
    function chargeCreditBalance($payData, $gatewayModel, $payable, $divider = 1, $payment_details_id = null, $required_credit = null, $tran_currency = null, $country_id = null): bool|float|int
    {
        try {
            $package = Package::find($payData['product_id']);
            $price = ($payData['amount_total'] / $divider) ?? $package->getFinalPrice() ?? 0;
            $item = $package;
            $payable = $payable ?? auth('seller')->user();
            $payment = $gatewayModel::getProviderName();
            $required_credit = !empty($package) ? $package?->credits : ($payData['required_credit'] ?? $required_credit ?? 0);
            if (!empty($required_credit)) {
                charge($required_credit)
                    ->to($payable)
                    ->overCharge()
                    ->meta([
                        'data' => $package ? $package->getWalletMeta()['data'] : __('wallet.buy_credit') . '[' . $required_credit . ']',
                        'package' => $package ? $package?->toArray() : null,
                    ])
                    ->after(fn(Transaction $tx) => SubscribeFrom::createPurchase(
                        price: $price,
                        item: $item ?? ($required_credit?$tx:null) ??null,
                        payment: $payment,
                        payable: $payable,
                        transaction_id: $payData['trans_ref'], //, $tx->id,
                        chargeable: $tx,
                        payment_detail_id: $payment_details_id,
                        is_form_wallet: 0,
                        currency: $tran_currency ?? $item->currency->code ?? $tx->currency,
                        country_id: $country_id ?? $payable->country_id ?? getCountryId(),
                        status: 1
                    )
                    )
                    ->commit();

            }
            if (!empty($required_credit)) {
                // Centralized Wafeq invoice creation
                if (config('services.wafeq.enabled') && ($payData['amount_total'] ?? 0) > 0) {
                    try {
                        $wafeq = new \App\Services\Accounting\WafeqService();
                        $wafeq->createWalletRechargeInvoice(
                            user: $payable,
                            amount: (float) $payData['amount_total'],
                            transactionId: $payData['trans_ref'],
                            currency: $tran_currency ?? 'SAR'
                        );
                    } catch (Exception $e) {
                        // Failures are already logged to failed_invoices table inside WafeqService
                        Log::error("Centralized Wafeq creation failed: " . $e->getMessage());
                    }
                }
                return $required_credit;
            }

            //$this->dispatch('refreshWallet');

        } catch (Exception $ex) {
            Notification::make()
                ->title(__('wallet.charge'))
                ->body($ex->getMessage())
                ->danger()
                ->persistent()
                ->send();


        }
        return false;
    }
}

if (!function_exists('getPremiumSellers')) {
    function getPremiumSellers($service_id): Builder
    {
        $service = Service::with('category')->find($service_id);

        $service_id = $service->id ?? -1;
        $sub_category_id = $service->category_id ?? $sub_category ?? -1;
        $main_category_id = $service->category?->parent_id ?? $main_category ?? -1;

        return Seller::query()->tenant()->where('blocked', false)
            ->whereHas('subscriptions',
                function ($query) use ($service_id, $main_category_id, $sub_category_id) {
                    $query->active()
                        ->where(fn($query) => $query->where('is_premium', true)
                            ->where(fn($query) => $query->Where('premium_items_limit', '=', -1)
                                ->orWhereHas('items', function ($query) use ($service_id, $main_category_id, $sub_category_id) {
                                    $query->where('type', SubscriptionPlanType::PREMIUM)
                                        ->where('main_category_id', $main_category_id)
                                        ->orWhere(function ($query) use ($service_id, $sub_category_id) {
                                            $query->where('sub_category_id', $sub_category_id)
                                                ->orWhere('service_id', $service_id);
                                        });
                                })
                            )
                        );
                });


    }
}

if (!function_exists('instanceOfAny')) {
    function instanceOfAny($component, array $types): bool
    {
        foreach ($types as $type) {
            if ($component instanceof $type) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('getCountry')) {
    function getCountry($code = null, $id = null, $subDomain = null): ?Country
    {
         if ($subDomain) {
             return Cache::remember("country_$subDomain", 60 * 30,
                 fn() =>
                 Country::where('slug',  $subDomain)->first()
             );
        }
        elseif ($code)
            return Cache::remember("country_$code", 60 * 30,
                function () use ($code) {
                    $country = Country::where("code", $code)->active()->first();
                    return $country ?? Country::find(app(GeneralSettings::class)->default_country) ?? Country::first();
                });

        elseif ($id)
            return Cache::remember("country_$id", 60 * 30,
                function () use ($id) {
                    $country = Country::where("id", $id)->active()->first();
                    return $country  ?? Country::first();
                });



        return Country::first();
    }
}
if (!function_exists('getTenant')) {
    function getTenant(): ?Country
    {

        $user = auth('customer')->user() ?? auth('seller')->user();
        if ($user && !empty($country = $user->country)) {
            return $country;
        } else {
            try {
                $geoDetect = new GeoDetect();
                $countryCode = $geoDetect->getCountry(request()->ip());
                return getCountry(code: $countryCode->getIsoCode());
//                            $country = Country::where("code", $countryCode->getIsoCode())->active()->first();
//                            return $country ?? Country::find(app(GeneralSettings::class)->default_country) ?? Country::first();

            } catch (Exception $e) {
                return getCountry(id: app(GeneralSettings::class)->default_country);
            }
        }
    }
}
if (!function_exists('isMobile')) {

    function isMobile()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mobile"));
        } else {
            return false;
        }
    }
}

if (!function_exists('isDesktop')) {

    function isDesktop()
    {
        return !isMobile();
    }
}

if (!function_exists('getTenantBySubDomain')) {
    function getTenantBySubDomain($subDomain = 'sa'): ?Country
    {
        return getCountry(subDomain: $subDomain);
    }
}

if (!function_exists('getCurrentTenant')) {
    function getCurrentTenant(): ?Country
    {
        $subdomain = getSubdomain();

        if ($subdomain != null) {

            $subdomainTenant = getTenantBySubDomain($subdomain);

            if ($subdomainTenant != null) {
                return $subdomainTenant;
            }
        }

        return getTenant();
    }
}

if (!function_exists('getCountryId')) {
    function getCountryId(): int
    {
        return getCurrentTenant()?->id ?? 1;
    }
}


if (!function_exists('getHost')) {
    function getHost(): string
    {
        $host = request()->getHost(); // Step 1: Extract the host
        $hostParts = explode('.', $host); // Step 2: Split the host by dots

        // Step 3: Check if there are more than 2 elements
        if (count($hostParts) > 2) {
            // Step 4: Reconstruct the host without subdomains
            $host = implode('.', array_slice($hostParts, -2));
        }
        return $host;
    }
}

if (!function_exists('getSubdomain')) {
    function getSubdomain(): ?string
    {
        $host = request()->getHost(); // Step 1: Extract the host
        $hostParts = explode('.', $host); // Step 2: Split the host by dots

        // Step 3: Check if there are more than 2 elements (indicating a subdomain is present)
        if (count($hostParts) > 2) {
            // Step 4: Extract the subdomain(s) by removing the last two elements (domain and TLD)
            $subdomain = implode('.', array_slice($hostParts, 0, -2));
            return $subdomain;
        }

        // Return null if no subdomain is present
        return null;
    }
}

if (!function_exists('getSkyPrefix')) {
    function getSkyPrefix(): string
    {
        return SkyPlugin::get()->getSkyPrefix();
    }
}

if (!function_exists('redirectToTenant')) {
    function redirectToTenant($tenant, $request): Response
    {
        $hostWithoutSubdomains = getHost();
        
        // For local development, use http and handle localhost/127.0.0.1 without subdomains
        if (app()->environment('local', 'testing')) {
            // In local development, don't redirect to subdomain, just use the current host
            if (in_array($hostWithoutSubdomains, ['localhost', '127.0.0.1'])) {
                return redirect()->to($request->getRequestUri());
            }
            $scheme = 'http';
        } else {
            $scheme = 'https';
        }
        
        // Fallback if tenant is null
        $slug = $tenant?->slug ?? 'sa';
        $redirectUrl = "{$scheme}://{$slug}.{$hostWithoutSubdomains}{$request->getRequestUri()}";

        return redirect()->to($redirectUrl);
    }
}

if (!function_exists('socialMediaLinks')) {
    function socialMediaLinks(): Collection
    {
        return collect([
            'instagram' => app(GeneralSettings::class)->instagram,
            'facebook' => app(GeneralSettings::class)->facebook,
            'twitter' => app(GeneralSettings::class)->twitter,
        ]);
    }
}

if (!function_exists('postStatus')) {
    function postStatus(): Collection
    {
        return collect([
            ['name' => 'publish', 'label' => __('string.post.statuses.publish'), 'class' => 'px-2 py-0.5 text-xs rounded-xl text-success-700 bg-success-500/10', 'icon' => 'heroicon-o-check-badge'],
            ['name' => 'future', 'label' => __('string.post.statuses.future'), 'class' => 'px-2 py-0.5 text-xs rounded-xl text-warning-700 bg-warning-500/10', 'icon' => 'heroicon-o-calendar-days'],
            ['name' => 'draft', 'label' => __('string.post.statuses.draft'), 'class' => 'px-2 py-0.5 text-xs rounded-xl text-custom-700 bg-custom-500/10', 'icon' => 'heroicon-o-document-magnifying-glass'],
            ['name' => 'auto-draft', 'label' => __('string.post.statuses.auto-draft'), 'class' => 'px-2 py-0.5 text-xs rounded-xl text-info-700 bg-info-500/10', 'icon' => 'heroicon-o-document-magnifying-glass'],
            ['name' => 'pending', 'label' => __('string.post.statuses.pending'), 'class' => 'px-2 py-0.5 text-xs rounded-xl text-info-700 bg-info-500/10', 'icon' => 'heroicon-o-document-minus'],
            ['name' => 'private', 'label' => __('string.post.statuses.private'), 'class' => 'px-2 py-0.5 text-xs rounded-xl text-danger-700 bg-danger-500/10', 'icon' => 'heroicon-o-lock-closed'],
            ['name' => 'trash', 'label' => __('string.post.statuses.trash'), 'class' => 'px-2 py-0.5 text-xs rounded-xl text-danger-700 bg-danger-500/10', 'icon' => 'heroicon-o-trash'],
            ['name' => 'inherit', 'label' => __('string.post.statuses.inherit'), 'class' => 'px-2 py-0.5 text-xs rounded-xl text-gray-700 bg-gray-500/10', 'icon' => 'heroicon-m-arrow-up-left'],
        ]);
    }
}
if (!function_exists('getTranslatedMonths')) {
    function getTranslatedMonths(): array
    {
        return  [
            __('months.Jan'), __('months.Feb'), __('months.Mar'), __('months.Apr'),
            __('months.May'), __('months.Jun'), __('months.Jul'), __('months.Aug'),
            __('months.Sep'), __('months.Oct'), __('months.Nov'), __('months.Dec')
        ];
    }
}


