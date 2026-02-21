<?php

namespace App\Filament\Resources\SellerResource\RelationManagers;

use App\Models\SellerProfileService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SellerProfileServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'sellerProfileServices';

    public static function getModelLabel(): string
    {
        return __('seller.services.single');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('seller.services.profile');
    }

    public static function getPluralLabel(): ?string
    {
        return __('seller.services.plural');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('service_title')
                    ->label(__('services.services.name'))
                    ->columnSpanFull()
                    ->required(),

                TextInput::make('service_description')
                    ->label(__('seller.seller_profile_services.service_description'))
                    ->columnSpanFull()
                    ->required(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('service_title')
                    ->label(__('services.services.name')),

                TextColumn::make('service_description')
                    ->label(__('seller.seller_profile_services.service_description'))

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
