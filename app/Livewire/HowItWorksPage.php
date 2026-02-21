<?php

namespace App\Livewire;

use App\Settings\HowSellerSettings;
use App\Settings\HowCustomerSettings;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Component;

class HowItWorksPage extends Page
{

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'livewire.how-it-works-page';

    public static function getNavigationLabel(): string
    {
        return __('string.how_it_works');
    }

    public function getTitle(): string|Htmlable
    {
        return __('string.how_it_works');
    }

    public static function getRoutePath(): string
    {
        return '/how-it-works/{page}';
    }

    public static function getRouteName(?string $panel = null): string
    {
        return 'how-it-works';
    }


    protected static ?string $slug = 'how-it-works';

    public $page;


    public function mount($page)
    {

        $this->page = $page;

    }

//    public function render()
//    {
//        return view('livewire.how-it-works-page');
//    }
}
