<?php

namespace App\Livewire;


use App\Settings\AboutSettings;
use Filament\Pages\Page;

use Illuminate\Contracts\Support\Htmlable;
use Livewire\Component;
use App\Settings\GeneralSettings;


class About extends Page
{


    public ?string $about_title;
    public ?string $about_sub_title;
    public ?string $content;
    public ?string $image;
    public ?string $location;
    public ?array $location_map;
    public ?string $phone;
    public ?string $email;

    public $lng;
    public $lat;


    public ?array $data = [
        'locaton' => null
    ];


    public function mount()
    {
        $this->about_title = app(AboutSettings::class)->getAboutTitle(app()->getLocale());
        $this->about_sub_title = app(AboutSettings::class)->getAboutSubTitle(app()->getLocale());
        $this->content = app(AboutSettings::class)->getAbout(app()->getLocale());
        $this->image = app(AboutSettings::class)->getAboutImage();
        $this->location = app(AboutSettings::class)->getLocationContent(app()->getLocale());
        $this->location_map = app(AboutSettings::class)->location_map;
        $this->phone = app(AboutSettings::class)->phone;
        $this->email = app(AboutSettings::class)->email;

        $this->lng = $this->location_map['lng']?? null;
        $this->lat = $this->location_map['lat']?? null;
    }

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'livewire.about';

    public static function getNavigationLabel(): string
    {
        return __('string.about-us');
    }

    public function getTitle(): string|Htmlable
    {
        return __('string.about-us');
    }

    public static function getRoutePath(): string
    {
        return '/about';
    }

    public static function getRouteName(?string $panel = null): string
    {
        return 'about';
    }


    protected static ?string $slug = 'about';

//    public function render()
//    {
//        return view('livewire.about');
//    }
}
