<?php

namespace App\Filament\Clusters\FrontEndSettings\Pages;

use App\Settings\SubscriptionGuideSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageSubscriptionGuideSettings extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $settings = SubscriptionGuideSettings::class;

    public static function getNavigationGroup(): ?string
    {
        return __('nav.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('subscriptions.subscription_guide');
    }

    public function getTitle(): string|Htmlable
    {
        return __('subscriptions.subscription_guide');
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Section::make(__('subscriptions.premium.single'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\RichEditor::make("premium_guide_ar")->label(__('string.arabic', ['attribute' => __('subscriptions.subscription_guide')]))->nullable(),
                        Forms\Components\RichEditor::make("premium_guide_en")->label(__('string.english', ['attribute' => __('subscriptions.subscription_guide')]))->nullable(),
                    ]),

        Forms\Components\Section::make(__('subscriptions.subscription_in_credit'))
            ->columns(2)
                    ->schema([
                        Forms\Components\RichEditor::make("credit_guide_ar")->label(__('string.arabic', ['attribute' => __('subscriptions.subscription_guide')]))->nullable(),
                        Forms\Components\RichEditor::make("credit_guide_en")->label(__('string.english', ['attribute' => __('subscriptions.subscription_guide')]))->nullable(),
                    ])

            ]);


    }
}
