<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FailedInvoiceResource\Pages;
use App\Models\FailedInvoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class FailedInvoiceResource extends Resource
{
    protected static ?string $model = FailedInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?int $navigationSort = 15;

    public static function getNavigationGroup(): ?string
    {
        return __('wallet.payments.nav_group');
    }

    public static function getNavigationLabel(): string
    {
        return 'Failed Invoices';
    }

    public static function getModelLabel(): string
    {
        return 'Failed Invoice';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Failed Invoices';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('resolved', false)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->copyable()
                    ->limit(20),
                
                Tables\Columns\TextColumn::make('invoiceable.name')
                    ->label('User/Seller')
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('invoice_type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'wallet_recharge',
                        'success' => 'service_payment',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'wallet_recharge' => 'Wallet',
                        'service_payment' => 'Service',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('SAR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->error_message),
                
                Tables\Columns\IconColumn::make('resolved')
                    ->label('Resolved')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('invoice_type')
                    ->options([
                        'wallet_recharge' => 'Wallet Recharge',
                        'service_payment' => 'Service Payment',
                    ]),
                Tables\Filters\TernaryFilter::make('resolved')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Resolved')
                    ->falseLabel('Unresolved'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton(),
                Tables\Actions\Action::make('mark_resolved')
                    ->label('Mark Resolved')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->iconButton()
                    ->visible(fn ($record) => !$record->resolved)
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['resolved' => true])),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Details')
                    ->schema([
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('Transaction ID')
                            ->disabled(),
                        Forms\Components\TextInput::make('invoice_type')
                            ->label('Type')
                            ->disabled(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->disabled(),
                        Forms\Components\TextInput::make('currency')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Error Details')
                    ->schema([
                        Forms\Components\Textarea::make('error_message')
                            ->label('Error Message')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('error_details')
                            ->label('Error Details')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('request_payload')
                            ->label('Request Payload')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('resolved')
                            ->label('Resolved'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFailedInvoices::route('/'),
        ];
    }
}
