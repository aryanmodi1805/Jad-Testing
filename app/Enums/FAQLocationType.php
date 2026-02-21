<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;


enum FAQLocationType : int implements HasLabel{
    case General = 1;
    case Customer = 2;
    case Seller = 3;


    public function getLabel(): ?string
    {
        return match ($this) {
            self::General => __('string.general'),
            self::Customer => __('string.customer'),
            self::Seller => __('string.seller'),

        };
    }

    public static function getType(string $type): FAQLocationType
    {

        foreach (self::cases() as $status) {
            if( $type === $status->name ){
                return $status;
            }
        }
        return FAQLocationType::General;
    }
}
