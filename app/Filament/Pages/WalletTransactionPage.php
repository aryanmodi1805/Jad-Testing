<?php

namespace App\Filament\Pages;

use App\Filament\Wallet\Actions\AdminTransactionAction;
use App\Filament\Wallet\Actions\ApplyCouponAction;
use App\Filament\Wallet\Actions\ChargeCreditAction;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Widgets\WalletBalanceStateTable;

class WalletTransactionPage extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static string $view = 'filament.pages.wallet-transaction-page';
protected static ?int $navigationSort = 6 ;

    public static function getNavigationGroup(): ?string
    {
        return __('nav.wallet_group');
    }
    public static function getNavigationLabel(): string
    {
        return __('wallet.transaction');
    }

    public function getTitle(): string|Htmlable
    {
        return __('wallet.transaction');
    }

    protected function getHeaderActions(): array
    {
        return [
            AdminTransactionAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WalletBalanceStateTable::class,
        ];
    }

}
