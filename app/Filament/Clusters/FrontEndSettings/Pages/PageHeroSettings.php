<?php

namespace App\Filament\Clusters\FrontEndSettings\Pages;

use App\Filament\Clusters\FrontEndSettings;
use App\Settings\HeroesSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;
use LaraZeus\Sky\Editors\TipTapEditor;

class PageHeroSettings extends SettingsPage
{

    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = HeroesSettings::class;

    protected static ?string $cluster = FrontEndSettings::class;

    public function getTitle(): string|Htmlable
    {
        return __('settings.page_hero_settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('settings.page_hero_settings');
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make("main_hero")
                    ->label(__('settings.hero_image'))
                    ->nullable()
                    ->openable()
                    ->previewable()
                    ->imageEditor()
                    ->imageCropAspectRatio('192:63')
                    ->imageResizeTargetHeight(632)
                    ->imageResizeTargetWidth(1920)
                    ->columnSpan(2)

                ,
                Forms\Components\FileUpload::make("sub_hero")
                    ->label(__('settings.sub_hero_image'))
                    ->nullable()
                    ->openable()
                    ->previewable()
                    ->imageEditor()
                    ->imageCropAspectRatio('192:32')
                    ->imageResizeTargetHeight(320)
                    ->imageResizeTargetWidth(1920)
                    ->columnSpan(2),

                \FilamentTiptapEditor\TiptapEditor::make('text_ar')
                    ->label(__('string.arabic', ['attribute' => __('settings.hero_text')]))
                    ->nullable()
                    ->rtlDirection()
                    ->columnSpan(2) ,

                \FilamentTiptapEditor\TiptapEditor::make('text_en')
                    ->label(__('string.english', ['attribute' => __('settings.hero_text')]))
                    ->nullable()

                    ->ltrDirection()
                    ->columnSpan(2)
                ->extraFieldWrapperAttributes(['style' => 'min-height:10rem !important'])

            ]);

    }
}
