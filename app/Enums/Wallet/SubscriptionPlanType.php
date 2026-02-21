<?php

namespace App\Enums\Wallet;

use Filament\Support\Contracts\HasLabel;

enum SubscriptionPlanType: string implements HasLabel
{
    case PREMIUM = 'premium';
    case CREDIT = 'credit';


    public function getLabel(): ?string
    {
        return match ($this) {
            self::PREMIUM => __('subscriptions.premium.title'),
            self::CREDIT => __('subscriptions.subscription_in_credit'),
        };
    }

    public function getColumnName(): ?string
    {
        return match ($this) {
            self::PREMIUM => 'is_premium',
            self::CREDIT => 'is_in_credit',
        };
    }

    public static function getOptions(): array
    {
        return array_combine(
            array_map(fn($case) => $case->value, self::cases()),
            array_map(fn($case) => $case->getLabel(), self::cases())
        );
    }
}
