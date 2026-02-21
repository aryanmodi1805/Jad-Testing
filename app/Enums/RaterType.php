<?php

namespace App\Enums;

use App\Models\Customer;
use App\Models\Seller;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum RaterType: string implements HasLabel, HasColor, HasIcon
{
    case Seller = Seller::class;
    case Customer = Customer::class;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Seller => __('accounts.sellers.single'),
            self::Customer => __('accounts.customers.single'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Seller => "heroicon-m-user-group",
            self::Customer => "heroicon-m-user",
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Seller => 'success',
            self::Customer => 'warning',
        };
    }
}
