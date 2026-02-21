<?php

namespace App\Enums\Wallet;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CreditType: string implements HasLabel
{
    case IN_MAIN_CATEGORY = 'in_main_category';
    case IN_SUB_CATEGORY = 'in_sub_category';
    case IN_SERVICE = 'in_service';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::IN_MAIN_CATEGORY => __('subscriptions.is_in_main_category'),
            self::IN_SUB_CATEGORY => __('subscriptions.is_in_sub_category'),
            self::IN_SERVICE => __('subscriptions.is_in_service'),
        };
    }

    public function getSingleLabel(): ?string
    {
        return match ($this) {
            self::IN_MAIN_CATEGORY =>__('subscriptions.premium.main_categories'),
            self::IN_SUB_CATEGORY => __('subscriptions.premium.sub_categories'),
            self::IN_SERVICE =>  __('subscriptions.premium.services') ,
        };
    }
    public function getColumnName(): ?string
    {
        return match ($this) {
            self::IN_MAIN_CATEGORY =>'main_category_id',
            self::IN_SUB_CATEGORY =>'sub_category_id',
            self::IN_SERVICE => 'service_id',
        };
    }
    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }
        return $options;
    }
    public  function getSuffix(): string
    {
        return match ($this) {
            self::IN_MAIN_CATEGORY => 'X' . self::IN_MAIN_CATEGORY->getColumnName(),
            self::IN_SUB_CATEGORY => 'X' . self::IN_SUB_CATEGORY->getColumnName(),
            self::IN_SERVICE => 'X' . self::IN_SERVICE->getColumnName(),
        };
    }
}
