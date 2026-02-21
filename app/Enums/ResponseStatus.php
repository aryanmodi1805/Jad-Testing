<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ResponseStatus: int implements HasLabel
{
    case Invited = 0;

    case Pending = 1;

    case Rejected = 2;

    case Cancelled = 3;
    case Hired = 4;
    case Archived = 5;


    public function getIcon(): ?string
    {
        return match ($this) {
            self::Invited => "heroicon-m-cursor-arrow-ripple",
            self::Pending => "heroicon-m-cursor-arrow-ripple",
            self::Rejected => "heroicon-m-stop",
            self::Cancelled => "heroicon-m-x-mark",
            self::Hired => "heroicon-m-check-circle",
            self::Archived => "heroicon-m-calendar",

        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Invited => 'primary',
            self::Pending => 'primary',
            self::Rejected, self::Cancelled => 'secondary',
            self::Hired => 'success',
            self::Archived => 'info',

        };
    }

    public function getChartColor(): string|array|null
    {
        return match ($this) {
            self::Invited => 'green',
            self::Pending => 'yellow',
            self::Rejected => 'red',
            self::Cancelled => 'orange',
            self::Hired => 'blue',
            self::Archived => 'gray',

        };
    }

    public function getOption(): string
    {
        return match ($this) {
            self::Invited => self::Invited->getLabel(),
            self::Pending => self::Pending->getLabel(),
            self::Rejected => self::Rejected->getLabel(),
            self::Cancelled => self::Cancelled->getLabel(),
            self::Hired => self::Hired->getLabel(),
            self::Archived => self::Archived->getLabel(),


        };
    }

    public function getLabel($local = null): ?string
    {
        return match ($this) {
            self::Invited => __("localize.response.type.invited", locale: $local),
            self::Pending => __("localize.response.type.pending", locale: $local),
            self::Rejected => __("localize.response.type.rejected", locale: $local),
            self::Cancelled => __("localize.response.type.cancelled", locale: $local),
            self::Hired => __("localize.response.type.hired", locale: $local),
            self::Archived => __("localize.response.type.archived", locale: $local),


        };
    }

    public static function getOptions(): array
    {
        return [
            self::Invited->value => self::Invited->getLabel(),
            self::Pending->value => self::Pending->getLabel(),
            self::Rejected->value => self::Rejected->getLabel(),
            self::Cancelled->value => self::Cancelled->getLabel(),
            self::Hired->value => self::Hired->getLabel(),
            self::Archived->value => self::Archived->getLabel(),


        ];
    }
}
