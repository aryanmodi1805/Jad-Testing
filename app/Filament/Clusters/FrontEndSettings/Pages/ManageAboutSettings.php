<?php

namespace App\Filament\Clusters\FrontEndSettings\Pages;

use App\Filament\Clusters\FrontEndSettings;
use App\Filament\Seller\Clusters\Settings;
use App\Settings\AboutSettings;
use App\Filament\Components\Map;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class ManageAboutSettings extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = AboutSettings::class;

    protected static ?string $cluster = FrontEndSettings::class;


    public function getTitle(): string|Htmlable
    {
        return __('settings.about_settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('settings.about_settings.title');
    }


    public function form(Form $form): Form
    {
        // Use country code (e.g., 'SA') instead of subdomain slug
        $countryCode = getCountryCode() ?? 'SA';
        $current_tenant = strtolower($countryCode); // Phone input expects lowercase

        return $form
            ->schema([
                PhoneInput::make("phone")
                    ->initialCountry($current_tenant)
                    ->defaultCountry($current_tenant)
                    ->onlyCountries([$current_tenant])
                    ->label(__('settings.about_settings.phone'))->nullable(),
                Forms\Components\TextInput::make("email")->label(__('settings.about_settings.email'))->nullable(),
                Forms\Components\TextInput::make("location_en")->label(__('settings.about_settings.location_en'))->nullable(),
                Forms\Components\TextInput::make("location_ar")->label(__('settings.about_settings.location_ar'))->nullable(),
                Map::make("location_map")
                    ->label(__('settings.about_settings.location_map'))
                    ->columnSpanFull()
                    ->reactive()
                    ->autocomplete(
                        fieldName: 'location_name',
                        countries: [getCountryCode()],
                        placeField: 'name'
                    )
                    ->defaultLocation(getTenant()?->location ?? ['lat' => 24.7136, 'lng' => 46.6753])
                    ->draggable() // allow dragging to move marker
                    ->clickable(true) // allow clicking to move marker
                    ->geolocate() // adds a button to request device location and set map marker accordingly
                    ->geolocateLabel('Get Location') // overrides the default label for geolocate button
                    ->geolocateOnLoad(true, false)
                    ->autocompleteReverse(true)
                    ->defaultZoom(6)// allow dragging to move marker
                    ->nullable(),


                Forms\Components\Section::make(__('settings.about_settings.about_us'))
                    ->schema([
                        Forms\Components\TextInput::make("about_title_en")->label(__('settings.about_settings.about_title_en'))->nullable(),
                        Forms\Components\TextInput::make("about_title_ar")->label(__('settings.about_settings.about_title_ar'))->nullable(),
                        Forms\Components\TextInput::make("about_sub_title_en")->label(__('settings.about_settings.about_sub_title_en'))->nullable(),
                        Forms\Components\TextInput::make("about_sub_title_ar")->label(__('settings.about_settings.about_sub_title_ar'))->nullable(),

                        Forms\Components\RichEditor::make("about_en")->label(__('settings.about_settings.about_en'))->nullable(),
                        Forms\Components\RichEditor::make("about_ar")->label(__('settings.about_settings.about_ar'))->nullable(),
                        Forms\Components\FileUpload::make("about_image")->label(__('settings.about_settings.about_image'))->nullable(),




                    ]),



            ]);


    }
}
