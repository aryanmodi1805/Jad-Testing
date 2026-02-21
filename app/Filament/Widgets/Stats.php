<?php

namespace App\Filament\Widgets;

use App\Models\Country;
use App\Models\Purchase;
use App\Models\Request;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Stats extends BaseWidget
{
    use InteractsWithPageFilters;
//    use HasWidgetShield;
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

        $data = Country::select('id')->withCount([
            'sellers' => fn($query) => $this->applyQueryFilter($query, $startDate, $endDate),
            'customers' => fn($query) => $this->applyQueryFilter($query, $startDate, $endDate),
            'services' => fn($query) => $this->applyQueryFilter($query, $startDate, $endDate),
            'requests' => fn($query) => $this->applyQueryFilter($query, $startDate, $endDate),
            'requests as purchased_requests' =>
                fn($query) => $this->applyQueryFilter($query, $startDate, $endDate)->whereHas('purchases', fn($q) => $q->where('status', 1)),
            'subscriptions' => fn($query) => $this->applyQueryFilter($query, $startDate, $endDate),
            'responses' => fn($query) => $this->applyQueryFilter($query, $startDate, $endDate, 'responses'),
        ])
            ->where('id',  filament()->getTenant()?->id ?? getTenant()?->id ?? 1)->first();

        if (!$data) {
            // Return empty stats if no country found
            return [
                Stat::make(__('accounts.sellers.plural'), '0')->chart([0])->color('primary'),
                Stat::make(__('accounts.customers.plural'), '0')->chart([0])->color('secondary'),
                Stat::make(__('seller.services.plural'), '0')->chart([0])->color('primary'),
                Stat::make(__('subscriptions.subscriptions'), '0')->chart([0])->color('success')->icon('heroicon-o-users'),
                Stat::make(__('seller.requests.plural'), '0')->chart([0])->color('warning'),
                Stat::make(__('seller.responses.plural'), '0')->chart([0])->color('success'),
            ];
        }

        return [

            Stat::make(__('accounts.sellers.plural'), number_format($data->sellers_count))->chart([2, 2])->color('primary'),

            Stat::make(__('accounts.customers.plural'), number_format($data->customers_count))->chart([2, 2])->color('secondary'),
            Stat::make(__('seller.services.plural'), number_format($data->services_count))->chart([2, 2,])->color('primary'),
            Stat::make(__('subscriptions.subscriptions'), number_format($data->subscriptions_count))->chart([1, 1,])->color('success')->icon('heroicon-o-users'),

            Stat::make(__('seller.requests.plural'), number_format($data->requests_count))
                ->chart([0, 0])->color('warning')
                ->description(__('string.request_has_been_bought', ['count' => $data->purchased_requests]))->descriptionColor('primary'),

//            Stat::make(__('string.requests_has_been_bought'), number_format($data->purchased_requests))->chart([2, 2])->color('success'),
            Stat::make(__('seller.responses.plural'), number_format($data->responses_count))->chart([2, 2])->color('success'),


        ];
    }

    private function applyQueryFilter($query, $startDate, $endDate, $table = null)
    {
        return $query->when($startDate, fn($query) => $query->whereDate($table ? $table . '.created_at' : 'created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->whereDate($table ? $table . '.created_at' : 'created_at', '<=', $endDate));

    }
}
