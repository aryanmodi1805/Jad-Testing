<?php

namespace App\Filament\Seller\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class AppSubscribePage extends SubscriptionPlans
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $slug = "app-subscribe";
    protected static string $layout= 'components.layouts.simple';
    protected static bool $shouldRegisterNavigation = false;
    public string $action_type = 'api';

//    protected static string $view = 'filament.seller.pages.app-subscribe-page';

protected function getHeaderWidgets(): array
{
    return[];
}

    protected function getLayoutData(): array
    {
        return [
            'hasTopbar' => false,
            'maxWidth' => '4xl',
        ];
    }

    public function hasLogo(): bool
    {
        return true;
    }
}
