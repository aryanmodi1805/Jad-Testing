<?php

namespace App\Filament\Clusters\FrontEndSettings\Pages;

use App\Filament\Clusters\FrontEndSettings;
use App\Settings\AuthSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class AuthPagesSettings extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = AuthSettings::class;

    protected static ?string $cluster = FrontEndSettings::class;

    public function getTitle(): string|Htmlable
    {
        return __('settings.auth_pages_settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('settings.auth_pages_settings');
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make("login_page_image")->label(__('settings.login_image'))->nullable()->columnSpan(2),
                Forms\Components\FileUpload::make("register_page_image")->label(__('settings.register_image'))->nullable()->columnSpan(2),
            ]);

    }
}
