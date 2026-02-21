<?php

namespace App\Filament\Resources\RequestResource\RelationManagers;

use App\Filament\Actions\TableChatAction;
use App\Filament\Resources\SellerResource;
use App\Models\Response;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Mokhosh\FilamentRating\Columns\RatingColumn;

class ResponsesRelationManager extends RelationManager
{
    protected static string $relationship = 'responses';

    public static function getModelLabel(): string
    {
        return __('seller.responses.single');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('seller.responses.plural');
    }

    public static function getPluralLabel(): ?string
    {
        return __('seller.responses.plural');
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('request_id')
                    ->label(__('requests.request_id')),

                TextColumn::make('seller.name')
                    ->label(__('columns.seller_name'))
                    ->url(function ($record) {
                            return SellerResource::getUrl('view', ['record' => $record->seller_id]);
                    })
                    ->openUrlInNewTab(),

                TextColumn::make('status')
                    ->label(__('labels.status')),

                TextColumn::make('notes')
                    ->wrap()
                    ->label(__('labels.notes')),

                IconColumn::make('is_approved')
                    ->boolean()
                    ->label(__('columns.approved')),

                RatingColumn::make('rate')
                    ->label(__('seller.rate.single')),

            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
                TableChatAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
