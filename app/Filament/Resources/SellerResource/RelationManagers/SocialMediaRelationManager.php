<?php

namespace App\Filament\Resources\SellerResource\RelationManagers;

use App\Models\SellerSocialMedia;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SocialMediaRelationManager extends RelationManager
{
    protected static string $relationship = 'socialMedia';

    public static function getModelLabel(): string
    {
        return __('seller.social_media.nav');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('seller.social_media.nav');
    }

    public static function getPluralLabel(): ?string
    {
        return __('seller.social_media.text');
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('platform')
                    ->label(__('seller.social_media.platform'))
                    ->required(),

                TextInput::make('link')
                    ->label(__('seller.social_media.link'))
                    ->required(),

                FileUpload::make('icon')
                    ->label(__('seller.social_media.icon')),
                Toggle::make('active')
                    ->label(__('columns.active'))

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([

                TextColumn::make('platform')
                    ->label(__('seller.social_media.platform')),

                TextColumn::make('link')
                    ->label(__('seller.social_media.link')),

                ImageColumn::make('icon')
                    ->label(__('seller.social_media.icon')),

                ToggleColumn::make('active')
                    ->label(__('columns.active'))


            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
