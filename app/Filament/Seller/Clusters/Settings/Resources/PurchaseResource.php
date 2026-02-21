<?php

namespace App\Filament\Seller\Clusters\Settings\Resources;

use App\Concerns\SubscribeFrom;
use App\Filament\Actions\RefundRequestTableAction;
use App\Filament\Seller\Clusters\Settings;
use App\Filament\Seller\Clusters\Settings\Resources\PurchaseResource\Pages;
use App\Filament\Seller\Clusters\Settings\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use App\Models\Seller;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 11;
    protected static bool $shouldSkipAuthorization = true;


    protected static ?string $cluster = Settings::class;

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->actionsPosition(ActionsPosition::BeforeCells)
            ->columns(SubscribeFrom::getPurchaseColumns())
            ->filters([
                //
            ])
            ->actions([
                RefundRequestTableAction::make(),
            ])
            ->bulkActions([

            ]);

    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePurchases::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('paymentDetails')
            ->whereMorphRelation(
                relation: 'payable',
                types: [Seller::class],
                column: 'payable_id',
                operator: '=',
                value: auth('seller')->id()
            );
    }
    public static function getNavigationGroup(): ?string
    {
        return __('wallet.payments.nav_group');
    }
    public function getTitle(): string|Htmlable
    {
        return __('wallet.purchases');
    }

    public static function getNavigationLabel(): string
    {
        return __('wallet.purchases');
    }

    public static function getModelLabel(): string
    {
        return __('wallet.purchase');
    }

    public static function getPluralLabel(): ?string
    {
        return __('wallet.purchases');
    }

}
