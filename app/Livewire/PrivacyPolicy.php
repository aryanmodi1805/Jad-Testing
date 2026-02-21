<?php

namespace App\Livewire;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Component;

class PrivacyPolicy extends Page
{

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'livewire.privacy-policy';

    public static function getNavigationLabel(): string
    {
        return __('footer.privacy-policy');
    }

    public function getTitle(): string|Htmlable
    {
        return __('footer.privacy-policy');
    }

    public static function getRoutePath(): string
    {
        return '/privacy-policy';
    }

    public static function getRouteName(?string $panel = null): string
    {
        return 'privacy-policy';
    }


    protected static ?string $slug = 'privacy_policy';
//    public function render()
//    {
//        return view('livewire.privacy-policy');
//    }
}
