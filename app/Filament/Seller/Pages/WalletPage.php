<?php

namespace App\Filament\Seller\Pages;

use App\Filament\Seller\Widgets\WalletBalanceStateTable;
use App\Filament\Seller\Widgets\WalletOverview;
use App\Filament\Wallet\Actions\ChargeCreditAction;
use App\Filament\Wallet\Actions\ChargePackageAction;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;

class WalletPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?int $navigationSort = 6;


    protected static string $view = 'filament.seller.pages.wallet-page';

    #[Url]
    public ?string $e = null;
    #[Url]
    public ?string $cr = null;
    #[Url]
    public ?string $status = null;
    public bool $hasError = false;

    public static function getNavigationLabel(): string
    {
        return __('wallet.wallet');
    }

    public function mount(Request $request)
    {
        $this->hasError = isset($this->e) && $this->e == '1';
        if ($this->hasError) {
            redirect()->to($request->url())->with('error', __('wallet.failed_to_charge'));
        }
        if ($this->hasError === false && !empty($this->cr)) {
            redirect()->to($request->url())->with('message', __('wallet.charge') . ': ' . __('wallet.added_to_balance', ['amount' => $this->cr]));
        }
    }

    public function getTitle(): string|Htmlable
    {
        return __('wallet.wallet');
    }

    protected function getHeaderActions(): array
    {
        return [
            ChargeCreditAction::make(),
            //  ApplyCouponAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            WalletOverview::class,
            WalletBalanceStateTable::class,

        ];
    }

}

