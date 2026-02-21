<?php

namespace App\Enums\Wallet;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SubscriptionStatus: int  implements HasLabel ,HasColor
{

    case PENDING = 0;
    case ACTIVE = 1;
    case CANCELLED = 2;
    case EXPIRED = 3;
    case REFUND = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => __('subscriptions.status.0'),
            self::ACTIVE => __('subscriptions.status.1'),
            self::CANCELLED => __('subscriptions.status.2'),
            self::EXPIRED => __('subscriptions.status.3'),
            self::REFUND => __('subscriptions.refund'),
        };
    }
    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::ACTIVE => 'success',
            self::CANCELLED, self::EXPIRED => 'danger',
            self::REFUND => 'info',
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
