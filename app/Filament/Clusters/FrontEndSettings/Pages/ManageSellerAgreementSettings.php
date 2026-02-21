<?php

namespace App\Filament\Clusters\FrontEndSettings\Pages;

use App\Filament\Clusters\FrontEndSettings;
use App\Filament\Seller\Clusters\Settings;
use App\Settings\SellerAgreementSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageSellerAgreementSettings extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $cluster = FrontEndSettings::class;



    protected static string $settings = SellerAgreementSettings::class;

    public function getTitle(): string|Htmlable
    {
        return __('settings.seller_agreement.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('settings.seller_agreement.title');
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\RichEditor::make("seller_agreement_en")->label(__('settings.seller_agreement.seller_agreement_en'))->nullable()->columnSpanFull(),
                Forms\Components\RichEditor::make("seller_agreement_ar")->label(__('settings.seller_agreement.seller_agreement_ar'))->nullable()->columnSpanFull(),


            ]);


    }
}
