<?php
namespace App\Livewire;

use App\Models\Partner;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Component;

class HomePage extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'livewire.home-page';
    public static function getNavigationLabel(): string
    {
        return __('auth.home');
    }

    public function getTitle(): string|Htmlable
    {
        return __('auth.home');
    }

    public static function getRoutePath(): string
    {
        return '/';
    }

    public static function getRouteName(?string $panel = null): string
    {
        return 'home';
    }



    protected static ?string $slug = 'home';

//    public function render(): \Illuminate\Contracts\View\View
//    {
//        return view('livewire.home-page');
//    }
}
