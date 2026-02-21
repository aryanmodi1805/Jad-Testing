<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlockReportResource\Pages;
use App\Filament\Resources\BlockReportResource\RelationManagers;
use App\Models\BlockReport;
use App\Models\Response;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class BlockReportResource extends Resource
{
    protected static ?string $model = BlockReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getPluralLabel(): ?string
    {
        return __('labels.block_reports');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('nav.groups.reports');
    }

    public static function getNavigationLabel(): string
    {
        return __('labels.block_reports');
    }
//    public static function form(Form $form): Form
//    {
//        return $form
//            ->schema([
//                Forms\Components\TextInput::make('reference_type')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('reference_id')
//                    ->maxLength(36),
//                Forms\Components\TextInput::make('blocker_type')
//                    ->required()
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('blocker_id')
//                    ->required()
//                    ->numeric(),
//                Forms\Components\TextInput::make('blocked_type')
//                    ->required()
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('blocked_id')
//                    ->required()
//                    ->numeric(),
//                Forms\Components\Select::make('block_reason_id')
//                    ->relationship('blockReason', 'name'),
//                Forms\Components\Textarea::make('details')
//                    ->columnSpanFull(),
//            ]);
//    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('blocker_type')
                    ->label(__('labels.blocker_type'))
                    ->getStateUsing(fn (BlockReport $blockReport) => $blockReport->blocker_type == 'App\\Models\\Customer' ? __('string.customer') :($blockReport->blocker_type == 'App\\Models\\Seller' ? __('string.seller') : __('string.other')))
                    ->searchable(),
                Tables\Columns\TextColumn::make('blocker_name')
                    ->label(__('labels.blocker_name'))
                    ->getStateUsing(fn (BlockReport $blockReport) => $blockReport->blocker->company_name ?? $blockReport->blocker->name)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('blocked_type')
                    ->label(__('labels.blocked_type'))
                    ->getStateUsing(fn (BlockReport $blockReport) => $blockReport->blocked_type == 'App\\Models\\Customer' ? __('string.customer') :($blockReport->blocked_type == 'App\\Models\\Seller' ? __('string.seller') : __('string.other')))
                    ->searchable(),
                Tables\Columns\TextColumn::make('blocked_name')
                    ->label(__('labels.blocked_name'))
                    ->getStateUsing(fn (BlockReport $blockReport) => $blockReport->blocked->company_name ?? $blockReport->blocked->name)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('blockReason.name')
                    ->label(__('labels.block_reason'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('labels.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
//                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('view request')
                    ->label(__('string.view_request'))
                    ->color('secondary')
                    ->url(
                        fn($record): string => route('filament.admin.resources.requests.view', [
                            'record' => Response::where('id', $record->reference_id)->value('request_id'),
                            'tenant'=>getTenant()
                        ])
                    ),
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBlockReports::route('/'),


        ];
    }
}
