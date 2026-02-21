<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RejectReason: string implements HasLabel
{
    case r1 = 'r1';
    case r2 = 'r2';
    case r3 = 'r3';
    case r4 = 'r4';
    case r5 = 'r5';
    case custom = 'custom';


    public function getLabel(): ?string
    {
       // return  __("interview.current_location.{$this}");



        return match ($this) {
            self::r1 => __("interview.reject_reason.r1"),
            self::r2 => __("interview.reject_reason.r2"),
            self::r3 => __("interview.reject_reason.r3"),
            self::r4 => __("interview.reject_reason.r4"),
            self::r5 => __("interview.reject_reason.r5"),
            self::custom => __("interview.reject_reason.custom"),

        };
    }



}
