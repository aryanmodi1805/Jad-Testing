<?php

namespace App\Livewire;

use App\Enums\FAQLocationType;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use LaraZeus\Sky\Models\Faq;
use LaraZeus\Sky\SkyPlugin;
use Livewire\Component;

class GeneralFaq extends Page
{

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'livewire.general-faq';

    public static function getNavigationLabel(): string
    {
        return __('string.faq.plural');
    }

    public function getTitle(): string|Htmlable
    {
        return __('string.faq.plural');
    }

    public static function getRoutePath(): string
    {
        return '/faq';
    }

    public static function getRouteName(?string $panel = null): string
    {
        return 'faq';
    }




    protected static ?string $slug = 'faq';

}
