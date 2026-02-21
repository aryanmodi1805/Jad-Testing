<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum RequestStatus: string implements HasLabel, HasColor,HasIcon
{
    case Pending = 'pending';
    case Open = 'open';
    case Booking = 'booking';
//    case Contacted = 'contacted';
//    case InProgress = 'inProgress';
    case Completed = 'completed';
//    case Closed = 'closed';
    case Rejected = 'rejected';

    public function getLabel($local = null): ?string
    {
        return match ($this) {
            self::Pending => __('localize.request.type.pending', locale: $local),
            self::Open => __('localize.request.type.open', locale: $local),
            self::Booking => __('localize.request.type.booking', locale: $local),
//            self::Contacted => __('localize.request.type.contacted'),
//            self::InProgress => __('localize.request.type.inProgress'),
            self::Completed =>__('localize.request.type.completed', locale: $local),
//            self::Closed => __('localize.request.type.closed'),
            self::Rejected => __('localize.request.type.rejected', locale: $local),
        };
    }

    public function getIcon(): ?string
    {
        // return  __("interview.current_location.{$this}");

        return match ($this) {
            self::Pending => "heroicon-m-clock",
            self::Open => "heroicon-m-folder-open",
            self::Booking => "heroicon-m-calendar",
//            self::Contacted => "heroicon-m-user",
//            self::InProgress => "heroicon-m-arrow-path",
            self::Completed => "heroicon-m-check",
//            self::Closed => "heroicon-m-lock-closed",
            self::Rejected => "heroicon-m-x-circle",
        };

    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Open => 'info',
            self::Booking => 'info',
//            self::Contacted => 'primary',
//            self::InProgress => 'secondary',
            self::Completed => 'success',
//            self::Closed => 'dark',
            self::Rejected => 'danger',
        };
    }


}
