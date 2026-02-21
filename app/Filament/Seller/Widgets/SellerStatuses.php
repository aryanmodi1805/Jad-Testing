<?php

namespace App\Filament\Seller\Widgets;

use App\Enums\ResponseStatus;
use App\Filament\Seller\Clusters\Settings\Pages\CompanyProfile;
use App\Filament\Seller\Clusters\Settings\Resources\SubscriptionResource\Pages\ManageSubscriptions;
use App\Filament\Seller\Pages\MyResponses;
use App\Filament\Seller\Pages\RequestDetails;
use App\Filament\Seller\Pages\WalletPage;
use App\Filament\Seller\Resources\RatingResource\Pages\ManageRatings;
use App\Filament\Seller\Resources\SellerLocationResource;
use App\Filament\Seller\Resources\SellerLocationResource\Pages\ListSellerLocations;
use App\Filament\Seller\Resources\SellerServiceResource\Pages\ManageSellerServices;
use App\Models\Request;
use App\Models\Seller;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class SellerStatuses extends BaseWidget
{

    protected static bool $isDiscovered = false;
    protected static ?string $pollingInterval = '350s';
    protected int|string|array $columnSpan = 2;

    protected function getColumns(): int
    {
        return 3;
    }
    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $sellerUser = auth('seller')->user();
        $seller = Seller::where('id',$sellerUser->id)->withCount([
            'responses as invited_responses_count' => fn($query) => $query->where('status', ResponseStatus::Invited),
            'responses as pending_responses_count' => fn($query) => $query->where('status',  ResponseStatus::Pending),
            'responses as hired_responses_count' => fn($query) => $query->where('status',  ResponseStatus::Hired),
            'responses as canceled_responses_count' => fn($query) => $query->whereIn('status',  [ResponseStatus::Cancelled, ResponseStatus::Rejected]),
            'sellerLocations as locations_count',
            'services as services_count',
            'activeSubscriptions as subscriptions_count',

        ])->first();

        $requests = Request::query()->canBeServedBySeller($sellerUser)->count();
        $balance = auth('seller')->user()->balance();

        return [
            Stat::make(__('string.seller_stats.profile_completion'), $seller->profileCompletionRate() . '%')
                ->chart([2, 2])->color('primary')->url(CompanyProfile::getUrl()),
            Stat::make(__('string.seller_stats.responses_hired'), $seller->hired_responses_count)
                ->description(__('string.seller_stats.responses_cancelled', ['count' =>  $seller->canceled_responses_count]))
                ->chart([2, 2])->color('secondary')->url(MyResponses::getUrl()),
            Stat::make(__('string.seller_stats.responses_pending'), $seller->pending_responses_count)
                ->chart([2, 2])->description(__('string.seller_stats.responses_invited', ['count' =>  $seller->invited_responses_count]))
                ->color('success')->url(MyResponses::getUrl()),

            Stat::make(__('string.seller_stats.requests'), $requests)->chart([2, 2])->color('primary')->url(RequestDetails::getUrl()),
//            Stat::make(__('string.seller_stats.subscriptions'), $seller->subscriptions_count)->chart([2, 2])->color('secondary')->url(ManageSubscriptions::getUrl()),
            Stat::make(__('string.seller_stats.wallet_balance'), $balance->value)->chart([2, 2])->color('info')->url(WalletPage::getUrl())
                ,
            Stat::make(__('string.seller_stats.locations'), $seller->locations_count)->chart([2, 2])->color('primary')->url(ListSellerLocations::getUrl()),
            Stat::make(__('string.seller_stats.services'), $seller->services_count)->chart([2, 2])->color('secondary')->url(ManageSellerServices::getUrl()),
            Stat::make(__('string.seller_stats.rate'), $sellerUser->rate)
                ->chart([2, 2])->description(__('string.seller_stats.rate_count', ['count' => $sellerUser->rate_count]))
                ->color('success')->url(ManageRatings::getUrl()),


        ];
    }

}
