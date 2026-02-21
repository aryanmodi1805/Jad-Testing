<?php

namespace App\Filament\Pages;

use App\Models\Country;
use App\Settings\GeneralSettings;
use App\Filament\Components\Map;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Filament\Forms\Components\Tabs;
use Illuminate\Contracts\Support\Htmlable;

class ManageGeneralSettings extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = GeneralSettings::class;

    public function getTitle(): string|Htmlable
    {
        return __('settings.general_settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('settings.general_settings.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('nav.settings');
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make("default_country")
                    ->label(__('settings.general_settings.default_country'))->columnSpanFull()
                    ->required()
                    ->options(Country::pluck('name', 'id'))->searchable(),

                Forms\Components\Toggle::make('request_status')
                    ->label(__('services.requests.status'))
                    ->onColor('success')
                    ->required()
                    ->offColor('danger')
                    ->inline(false)
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-x-circle')
                    ->helperText(__('services.requests.set_status')),
                Forms\Components\Toggle::make('show_subscriptions_page')
                    ->label(__('labels.show_subscriptions_plans_page'))
                    ->onColor('success')
                    ->required()
                    ->offColor('danger')
                    ->inline(false)
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-x-circle')
                    ->helperText(__('labels.show_subscriptions_plans_page_hint')),

                TextInput::make('maximum_responses')
                    ->label(__('responses.maximum_responses'))
                    ->numeric()
                    ->required()
                    ->helperText(__('responses.set_maximum_responses')),

                TextInput::make('fast_response_badge')
                    ->required()
                    ->label(__('string.fast_response_badge'))
                    ->suffix(__('string.minutes'))
                    ->numeric(),

                TextInput::make('regular_customer_badge')
                    ->required()
                    ->label(__('string.regular_customer_badge'))
                    ->suffix(__('services.requests.count'))
                    ->numeric(),
                TextInput::make('customers_count')
                    ->required()
                    ->label(__('string.satisfied-clients'))
                    ->numeric(),

                TextInput::make('teams_count')
                    ->required()
                    ->label(__('string.support-teams'))
                    ->numeric(),

                TextInput::make('projects_completed')
                    ->required()
                    ->label(__('string.projects-completed'))
                    ->numeric(),


            ]);


    }
}
