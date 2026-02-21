<?php

namespace App\Filament\Seller\Clusters\Settings\Resources;

use App\Filament\Seller\Clusters\Settings;
use App\Filament\Seller\Clusters\Settings\Resources\SubscriptionResource\Pages;
use App\Filament\Seller\Clusters\Settings\Resources\SubscriptionResource\RelationManagers;
use App\Models\Subscription;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;


    protected static ?string $cluster = Settings::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static bool $isScopedToTenant = true;
    protected static bool $shouldSkipAuthorization = true;

    public static function canCreate(): bool
    {
        return false;
    }

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

    public static function getNavigationLabel(): string
    {
        return __('subscriptions.my_subscriptions');
    }

    public static function getModelLabel(): string
    {
        return __('subscriptions.my_subscriptions');
    }

    public static function getPluralLabel(): ?string
    {
        return __('subscriptions.my_subscriptions');
    }
    public static function getNavigationGroup(): ?string
    {
        return __('wallet.payments.nav_group');
    }

    public static function table(Table $table): Table
    {
        $table= \App\Filament\Resources\SubscriptionResource::table($table);
        return $table->actions(
            \App\Filament\Resources\SubscriptionResource::getTableActions()
        );

    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSubscriptions::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('seller_id', auth('seller')->user()->id)->latest('updated_at');
    }
}
