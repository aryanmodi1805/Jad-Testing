<?php

namespace App\Filament\Clusters\FrontEndSettings\Pages;

use App\Filament\Clusters\FrontEndSettings;
use App\Filament\Seller\Clusters\Settings;
use App\Settings\SocialMediaSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageSocialMediaSettings extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = SocialMediaSettings::class;

    protected static ?string $cluster = FrontEndSettings::class;

    public function getTitle(): string|Htmlable
    {
        return __('settings.social_media_settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('settings.social_media_settings.title');
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                    Forms\Components\TextInput::make("instagram")->label(__('settings.social_media_settings.instagram'))
                        ->url(fn($state) => $state != null)
                        ->prefixIcon("tabler-brand-instagram")->nullable()
                    ,
                    Forms\Components\TextInput::make("facebook")->label(__('settings.social_media_settings.facebook'))
                        ->url(fn($state) => $state != null)
                        ->prefixIcon("tabler-brand-facebook")->nullable(),

                    Forms\Components\TextInput::make("linkedin")->label(__('settings.social_media_settings.linked_in'))
                        ->url(fn($state) => $state != null)
                        ->prefixIcon("tabler-brand-linkedin")->nullable(),

                    Forms\Components\TextInput::make("x")->label(__('settings.social_media_settings.x'))
                        ->url(fn($state) => $state != null)
                        ->prefixIcon("tabler-brand-x")->nullable(),
                        
                    Forms\Components\TextInput::make("youtube")->label(__('settings.social_media_settings.youtube'))
                        ->url(fn($state) => $state != null)
                        ->prefixIcon("tabler-brand-youtube")->nullable(),
                        
                    Forms\Components\TextInput::make("tiktok")->label(__('settings.social_media_settings.tiktok'))
                        ->url(fn($state) => $state != null)
                        ->prefixIcon("tabler-brand-tiktok")->nullable(),



            ]);


    }
}
