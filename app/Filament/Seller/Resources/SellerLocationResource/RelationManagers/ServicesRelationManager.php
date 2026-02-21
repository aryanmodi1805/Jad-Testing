<?php

namespace App\Filament\Seller\Resources\SellerLocationResource\RelationManagers;

use App\Models\SellerLocation;
use App\Models\SellerService;
use App\Models\SellerServiceLocation;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'sellerServiceLocations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('seller_service_id')
                    ->label(__('string.Service'))
                    ->options(fn($get) =>
                        SellerService::query()
                            ->where('seller_id', auth('seller')->id())
                            ->where(fn(Builder $query) =>
                                $query->whereNotIn('id',SellerServiceLocation::query()->where('seller_location_id', $this->ownerRecord?->id )->pluck('seller_service_id'))
                                    ->orWhere('id', $get('seller_service_id')))
                            ->get()
                            ->pluck('service.name', 'id'))
                    ->searchable()
                    ->columnSpanFull()
                    ->preload()
                    ->required(),

                Toggle::make('is_nationwide')
                    ->default($this->ownerRecord->is_nationwide ?? false)
                    ->live()
                    ->inline(false)
                    ->label(__('columns.is_nationwide')),

                Select::make('location_range')
                    ->default($this->ownerRecord->is_nationwide ? null : ($this->ownerRecord->range ?? 1))
                    ->options(__('string.distance_ranges'))
                    ->requiredIf('is_nationwide', false)
                    ->dehydratedWhenHidden()
                    ->dehydrateStateUsing(fn($component , $state) => $component->isHidden() ? null : $state)
                    ->hidden(fn($get) => $get('is_nationwide'))
                    ->label(__('columns.select_range')),

                Hidden::make('seller_id')
                    ->dehydrateStateUsing(fn() => auth('seller')->id()),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(__('string.attach_service_location'))
            ->recordTitleAttribute('service.name')
            ->heading(__('string.related_services_location'))
            ->columns([
                TextColumn::make('service.name')
                    ->label(__('columns.service'))
,
                ToggleColumn::make('is_nationwide')
                    ->updateStateUsing(fn($state, $record) =>  $state ? $record->update([
                        'is_nationwide' => true,
                        'location_range' => null]) : $record->update([
                        'is_nationwide' => false , 'location_range' => $record->sellerLocation->location_range ?? 1]))
                    ->label(__('columns.is_nationwide')),

                SelectColumn::make('location_range')
                    ->disabled(fn($record) => $record?->is_nationwide)
                    ->options(__('string.distance_ranges'))
                    ->label(__('columns.range'))->extraAttributes(fn($record) => $record?->is_nationwide ?  [
                        'style' => 'display:none'
                    ] : []),

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
