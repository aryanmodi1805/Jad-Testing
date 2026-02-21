<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentGoogleAnalytics\Widgets;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class VisitorsDashboard extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-m-chart-bar';

    protected static string $view = 'filament.pages.vistors-dashboard';
    protected static ?int $navigationSort = 100;
    protected static bool $shouldRegisterNavigation =true;

    public static function getNavigationGroup(): ?string
    {
        return __('nav.groups.reports');
    }

    public static function getNavigationLabel(): string
    {
        return __('nav.visitors_dashboard');
    }

    public function getTitle(): string|Htmlable
    {
        return __('nav.visitors_dashboard');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            Widgets\PageViewsWidget::class,
            Widgets\VisitorsWidget::class,
            Widgets\ActiveUsersOneDayWidget::class,
            Widgets\ActiveUsersSevenDayWidget::class,
            Widgets\ActiveUsersTwentyEightDayWidget::class,
            Widgets\SessionsWidget::class,
            Widgets\SessionsDurationWidget::class,
            Widgets\SessionsByCountryWidget::class,
            Widgets\SessionsByDeviceWidget::class,
            Widgets\MostVisitedPagesWidget::class,
            Widgets\TopReferrersListWidget::class,
        ];
    }
}
