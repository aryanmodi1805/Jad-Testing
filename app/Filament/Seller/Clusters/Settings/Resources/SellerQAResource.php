<?php

namespace App\Filament\Seller\Clusters\Settings\Resources;

use App\Filament\Seller\Clusters\Settings;
use App\Filament\Seller\Clusters\Settings\Resources\SellerQAResource\Pages;
use App\Filament\Seller\Clusters\Settings\Resources\SellerQAResource\RelationManagers;
use App\Forms\Components\TranslatableGrid;
use App\Models\SellerQA;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SellerQAResource extends Resource
{
    protected static ?string $model = SellerQA::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Settings::class;
    protected static bool $shouldSkipAuthorization = true;

    public function getTitle(): string
    {
        return __('seller.q_a_s.q_a_s');
    }

    public function getHeading(): string
    {
        return __('seller.q_a_s.q_a_s');
    }

    public static function getNavigationLabel(): string
    {
        return __('seller.q_a_s.q_a_s');

    }
    public function getSubheading(): ?string
    {
        return __('seller.q_a_s.q_a_s');
    }

    public static function getModelLabel(): string
    {
        return __('seller.q_a_s.q_a_s_single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('seller.q_a_s.q_a_s');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('seller_id')
                    ->dehydrateStateUsing(fn() => auth('seller')->id()),

                TranslatableGrid::make()->textInput('question')
                    ->label(__('seller.q_a_s.question')),

                TranslatableGrid::make()->textInput('answer')
                    ->label(__('seller.q_a_s.answer')),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question')
                    ->label(__('seller.q_a_s.question'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('answer')
                ->label(__('seller.q_a_s.answer')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ManageSellerQAS::route('/'),

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('seller_id', auth('seller')->id());
    }
}
