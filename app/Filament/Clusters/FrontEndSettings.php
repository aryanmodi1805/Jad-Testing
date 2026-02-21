<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class FrontEndSettings extends Cluster
{

    protected static ?string $slug = 'front_end';

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 1 ;
    public static function getClusterBreadcrumb(): ?string
    {
        return __('nav.settings');
    }
    public static function getNavigationGroup(): string
    {
        return __('nav.settings');
    }
    public static function getNavigationLabel(): string
    {
        return __('nav.front_end');
    }
    public function getTitle(): string
    {
        return __('nav.settings');
    }


}
