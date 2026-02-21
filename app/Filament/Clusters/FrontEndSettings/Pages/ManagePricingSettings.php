<?php

namespace App\Filament\Clusters\FrontEndSettings\Pages;

use App\Filament\Clusters\FrontEndSettings;
use App\Filament\Seller\Clusters\Settings;
use App\Settings\PricingSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManagePricingSettings extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = PricingSettings::class;

    protected static ?string $cluster = FrontEndSettings::class;


    public function getTitle(): string|Htmlable
    {
        return __('settings.pricing.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('settings.pricing.title');
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
        Forms\Components\Section::make(__('settings.step_1'))
                ->schema([
                    Forms\Components\TextInput::make("step_1_title_en")->label(__('settings.step_title_en'))->nullable()->maxLength(50),
                    Forms\Components\TextInput::make("step_1_title_ar")->label(__('settings.step_title_ar'))->nullable()->maxLength(50),
                    Forms\Components\Textarea::make("step_1_description_en")->label(__('settings.step_description_en'))->nullable()->maxLength(350),
                    Forms\Components\Textarea::make("step_1_description_ar")->label(__('settings.step_description_ar'))->nullable()->maxLength(350),
                    Forms\Components\FileUpload::make("step_1_image")->label(__('settings.step_image'))->nullable(),





                ]),
                Forms\Components\Section::make(__('settings.step_2'))
                    ->schema([

                        Forms\Components\TextInput::make("step_2_title_en")->label(__('settings.step_title_en'))->nullable()->maxLength(50),
                        Forms\Components\TextInput::make("step_2_title_ar")->label(__('settings.step_title_ar'))->nullable()->maxLength(50),
                        Forms\Components\Textarea::make("step_2_description_en")->label(__('settings.step_description_en'))->nullable()->maxLength(350),
                        Forms\Components\Textarea::make("step_2_description_ar")->label(__('settings.step_description_ar'))->nullable()->maxLength(350),
                        Forms\Components\FileUpload::make("step_2_image")->label(__('settings.step_image'))->nullable(),



                    ]),
                Forms\Components\Section::make(__('settings.step_3'))
                    ->schema([

                        Forms\Components\TextInput::make("step_3_title_en")->label(__('settings.step_title_en'))->nullable()->maxLength(50),
                        Forms\Components\TextInput::make("step_3_title_ar")->label(__('settings.step_title_ar'))->nullable()->maxLength(50),
                        Forms\Components\Textarea::make("step_3_description_en")->label(__('settings.step_description_en'))->nullable()->maxLength(350),
                        Forms\Components\Textarea::make("step_3_description_ar")->label(__('settings.step_description_ar'))->nullable()->maxLength(350),
                        Forms\Components\FileUpload::make("step_3_image")->label(__('settings.step_image'))->nullable(),




                    ]),
                Forms\Components\Section::make(__('settings.step_4'))
                    ->schema([

                        Forms\Components\TextInput::make("step_4_title_en")->label(__('settings.step_title_en'))->nullable()->maxLength(50),
                        Forms\Components\TextInput::make("step_4_title_ar")->label(__('settings.step_title_ar'))->nullable()->maxLength(50),
                        Forms\Components\Textarea::make("step_4_description_en")->label(__('settings.step_description_en'))->nullable()->maxLength(350),
                        Forms\Components\Textarea::make("step_4_description_ar")->label(__('settings.step_description_ar'))->nullable()->maxLength(350),
                        Forms\Components\FileUpload::make("step_4_image")->label(__('settings.step_image'))->nullable(),




                    ]),
            ]);

    }
}
