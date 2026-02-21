<?php

namespace App\Filament\Pages;

use App\Settings\WalletSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class WalletSettingPage extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = WalletSettings::class;
    protected static bool $isScopedToTenant = true;
    protected static bool $isDiscovered = false;

    public static function getNavigationGroup(): ?string
    {
        return __('nav.wallet_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('nav.credit_setting');
    }

    public function getTitle(): string|Htmlable
    {
        return __('nav.credit_setting');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('credit_price')
                    ->label(__('nav.credit_cost'))
                    ->required(),
            ]);
    }
}
