<?php

namespace App\Filament\Resources\SellerResource\RelationManagers;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\Components\Rating;

class RatingsRelationManager extends RelationManager
{
    protected static string $relationship = 'ratings';
    protected function canCreate(): bool
    {
        return false;
    }

    /**
     * @return string|null
     */
    public static function getPluralModelLabel(): ?string
    {
        return __('seller.rate.plural');
    }

    /**
     * @return string|null
     */
    public static function getLabel(): ?string
    {
        return __('seller.rate.plural');
    }

    /**
     * @return string|null
     */
    public static function getModelLabel(): ?string
    {
        return __('seller.rate.plural');
    }

    public function getContentTabIcon(): ?string
    {
        return 'heroicon-o-star';
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('seller.rate.plural');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Rating::make('rating')
                    ->formatStateUsing(fn($record, $state): float => $state)
                    ->label(__('seller.rate.single')) ,
                Textarea::make('review')->label(__('seller.rate.review')),
                Toggle::make('approved')->label(__('seller.rate.approved'))->default(true)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('review')
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('rater.name')->label(__('seller.rate.rater')),
                Tables\Columns\TextColumn::make('review')->label(__('seller.rate.review')),
                RatingColumn::make('rating')->label(__('seller.rate.single')),
                Tables\Columns\ToggleColumn::make('approved')->label(__('seller.rate.approved')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created Date')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Modified Date')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
