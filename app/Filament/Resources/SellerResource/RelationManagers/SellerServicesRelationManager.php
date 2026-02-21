<?php

namespace App\Filament\Resources\SellerResource\RelationManagers;

use App\Models\SellerService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class SellerServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'sellerServices';

    public static function getModelLabel(): string
    {
        return __('seller.services.single');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('seller.services.plural');
    }

    public static function getPluralLabel(): ?string
    {
        return __('seller.services.plural');
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('service_id')
                    ->relationship('service', 'name', function ($query, $record) {
                        return ($query->where(fn($query)=>
                        $query->notAssignedToSeller(auth('seller')->id())
                        ->when($record, fn($q) => $q->orWhere('id', $record->service_id))
                        )->where('country_id',getCountryId()));
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label(__('columns.service')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')

            ->columns([

                TextColumn::make('service.name')
                    ->searchable()
                    ->label(__('columns.service_name')),
                TextColumn::make('locations')
                    ->label(__('columns.locations'))
                    ->formatStateUsing(fn($state, $record) => $record->locations->count())
                    ->sortable(false)
                    ->searchable(false),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('columns.created_date')),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('columns.updated_date')),
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
