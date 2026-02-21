<?php

namespace App\Livewire;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Component;

class CustomerAgreement extends Page
{

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'livewire.customer-agreement';

    public static function getNavigationLabel(): string
    {
        return __('string.customer-agreement');
    }

    public function getTitle(): string|Htmlable
    {
        return __('string.customer-agreement');
    }

    public static function getRoutePath(): string
    {
        return '/customer-agreement';
    }

    public static function getRouteName(?string $panel = null): string
    {
        return 'customer-agreement';
    }


    protected static ?string $slug = 'customer-agreement';
//    public function render()
//    {
//        return view('livewire.customer-agreement');
//    }
}
