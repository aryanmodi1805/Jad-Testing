<?php

namespace App\Filament\Seller\Clusters;

use Filament\Clusters\Cluster;

class Settings extends Cluster
{
    protected static ?string $slug = 'settings';


    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return __('nav.settings');
    }
    public static function getNavigationLabel(): string
    {
        return __('nav.settings');
    }
    public function getTitle(): string
    {
        return __('nav.settings');
    }

}
