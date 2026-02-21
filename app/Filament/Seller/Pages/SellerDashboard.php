<?php

namespace App\Filament\Seller\Pages;

use App\Filament\Actions\RatingAction;
use App\Filament\Seller\Widgets\SellerStatuses;
use App\Filament\Seller\Widgets\ResponseChart;
use App\Filament\Seller\Widgets\WalletOverview;
use App\Models\Country;
use App\Models\Request;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Livewire\Attributes\Url;

class SellerDashboard extends Dashboard
{
    protected static string $view = 'filament.seller.pages.seller-dashboard';

    #[Url]
    public $action;

    public bool $showPleaseUseAppModal = false;

    public function mount(): void
    {
        if (session('please_use_app')) {
            $this->showPleaseUseAppModal = true;
            session()->forget('please_use_app');
        }
    }

    public function closePleaseUseAppModal(): void
    {
        $this->showPleaseUseAppModal = false;
        // Redirect to dashboard to refresh the page
        $this->redirect(static::getUrl(tenant: getCurrentTenant()));
    }

    public function getActions(): array
    {
        return [

            RatingAction::make()
                ->label(__('localize.site_reviews.single'))
                ->rateable(Country::find(getCountryId()))
                ->rater(auth('seller')->user()),

            RatingAction::make()
                ->name('rating-request-action')
                ->visible($this->action === 'rating-request-action' && session()->exists('request_id'))
                ->label(__('localize.request_rating.single'))
                ->rateable(Request::find(session('request_id')))
                ->rater(auth('seller')->user()),
        ];
    }
    public function getHeaderWidgetsColumns(): int|string|array
    {
        return 4;
    }

    public function getHeaderWidgets(): array
    {
        return [
            SellerStatuses::class,
            ResponseChart::class,
        ];
    }


}
