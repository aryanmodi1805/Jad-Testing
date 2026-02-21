<?php

namespace App\Enums;

use App\Models\Country;
use App\Models\Request;
use App\Models\Response;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum RateableType: string implements HasLabel, HasColor, HasIcon
{
    case Request = 'App\Models\Request';
    case Response ='App\Models\Response';
    case Country = 'App\Models\Country';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Request => __('services.requests.single'),
            self::Response => __('responses.Response'),
            self::Country => __('string.site'),
        };
    }


    public function getIcon(): ?string
    {
        return match ($this) {
            self::Request => "heroicon-m-briefcase",
            self::Response => "heroicon-m-chat-alt",
            self::Country => "heroicon-m-user",
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Request => 'primary',
            self::Response => 'success',
            self::Country => 'info',
        };
    }
}
