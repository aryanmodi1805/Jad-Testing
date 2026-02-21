<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Forms\Components\Translatable;
use App\Forms\Components\TranslatableGrid;
use App\Models\PaymentMethod;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $isScopedToTenant = true;
    protected static ?int $navigationSort = 15;

    public static function getNavigationGroup(): ?string
    {
        return __('wallet.payments.nav_group');
    }

    public static function getPluralModelLabel(): string
    {
        return __('wallet.payments.plural');
    }

    public static function getModelLabel(): string
    {
        return __('wallet.payments.single');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                TranslatableGrid::make()->textInput()
                    ->required()
                    ->columns(2)
                    ->label(__('wallet.payments.name'))
                    ->required(),

                Forms\Components\FileUpload::make('logo')
                    ->label(__('wallet.payments.logo'))
                    ->imageEditor()
                    ->multiple(false)
                    ->directory('PaymentLogos')
                    ->deletable()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']),

                Forms\Components\KeyValue::make('details')
                    ->label(__('wallet.payments.account_details'))
                    ->editableKeys(false)->addable(false)->deletable(false)
                    ->visible(fn($state) => !empty($state)),
                Forms\Components\KeyValue::make('additional_fields')->label(__('wallet.payments.extra_data'))
                    ->visible(fn($state) => !empty($state))
                    ->editableKeys(false)->addable(false)->deletable(false),
                Forms\Components\Toggle::make('active')->label(__('string.active'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('wallet.payments.name')),
                Tables\Columns\IconColumn::make('active')->label(__('string.active'))
                    ->boolean(),
                Tables\Columns\ImageColumn::make('logo')->label(__('wallet.payments.logo'))
                    ->size(40),

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                //
            ])->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
