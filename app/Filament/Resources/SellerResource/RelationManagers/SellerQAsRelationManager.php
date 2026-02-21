<?php

namespace App\Filament\Resources\SellerResource\RelationManagers;

use App\Models\SellerQA;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use LaraZeus\Sky\Editors\RichEditor;

class SellerQAsRelationManager extends RelationManager
{
    protected static string $relationship = 'sellerQAs';

    public static function getModelLabel(): string
    {
        return __('seller.q_a_s.q_a_s_single');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('seller.q_a_s.q_a_s');
    }

    public static function getPluralLabel(): ?string
    {
        return __('seller.q_a_s.q_a_s');
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Select::make('q_a_s_id')
                    ->label(__('columns.question'))
                    ->relationship('qAS', 'question')
                    ->searchable()
                    ->preload()
                    ->required(),

                \Filament\Forms\Components\RichEditor::make('answer')
                    ->columnSpanFull()
                    ->label(__('columns.answer'))
                    ->required(),


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('question')
                    ->label(__('columns.question'))
                    ->searchable()
                    ->wrap()
                    ->sortable(),
                TextColumn::make('answer')
                    ->label(__('columns.answer'))
                    ->wrap()
                    ->html(),
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
