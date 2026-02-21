<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlockReasonResource\Pages;
use App\Filament\Resources\BlockReasonResource\RelationManagers;
use App\Forms\Components\TranslatableGrid;
use App\Models\BlockReason;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BlockReasonResource extends Resource
{
    protected static ?string $model = BlockReason::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getNavigationGroup(): ?string
    {
        return __('nav.groups.reports');
    }

    public static function getNavigationLabel(): string
    {
        return __('labels.block_reasons');
    }
    public static function getPluralLabel(): ?string
    {
        return __('labels.block_reasons');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TranslatableGrid::make()->textInput('name')->label( __('string.name'))->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name.ar')
                    ->getStateUsing(fn(BlockReason $record) => $record->getTranslation('name','ar'))
                    ->label(__('string.arabic',['attribute'=> __('string.name')])),
                Tables\Columns\TextColumn::make('name.en')
                    ->getStateUsing(fn(BlockReason $record) => $record->getTranslation('name','en'))

                    ->label(__('string.english',['attribute'=> __('string.name')])),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBlockReasons::route('/'),
        ];
    }
}
