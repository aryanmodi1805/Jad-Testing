<?php

namespace App\Livewire;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Component;

class SellerAgreement extends Page
{

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'livewire.seller-agreement';

    public static function getNavigationLabel(): string
    {
        return __('string.seller-agreement');
    }

    public function getTitle(): string|Htmlable
    {
        return __('string.seller-agreement');
    }

    public static function getRoutePath(): string
    {
        return '/seller-agreement';
    }

    public static function getRouteName(?string $panel = null): string
    {
        return 'seller-agreement';
    }
//    public function render()
//    {
//        return view('livewire.seller-agreement');
//    }
}
