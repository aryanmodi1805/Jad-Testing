<?php

namespace App\Filament\Clusters\FrontEndSettings\Pages;

use App\Filament\Clusters\FrontEndSettings;
use App\Filament\Seller\Clusters\Settings;
use App\Settings\PrivacySettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManagePrivacySettings extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $cluster = FrontEndSettings::class;


    protected static string $settings = PrivacySettings::class;

    public function getTitle(): string|Htmlable
    {
        return __('settings.privacy_policy.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('settings.privacy_policy.title');
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\RichEditor::make("privacy_policy_en")->label(__('settings.privacy_policy.privacy_policy_en'))->nullable()->columnSpanFull(),
                Forms\Components\RichEditor::make("privacy_policy_ar")->label(__('settings.privacy_policy.privacy_policy_ar'))->nullable()->columnSpanFull(),


            ]);


    }
}
