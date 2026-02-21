<?php

namespace App\Filament\Seller\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

#[On('refreshWallet')]
class WalletOverview extends BaseWidget
{

    protected static bool $isDiscovered = false;
    protected static ?string $pollingInterval = null;
    protected function getStats(): array
    {
        $balance = auth('seller')->user()->balance();
        return [
            Stat::make(__('wallet.balance'), $balance->value)
                ->description(__('wallet.credits')),

        ];
    }
}
