<?php

namespace App\Filament\Seller\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use O21\LaravelWallet\Models\Transaction;

class WalletTransactionTable extends BaseWidget
{
    protected static bool $isDiscovered = false;
    protected int|string|array $columnSpan = 'full';

    /**
     * @return string|null
     */
    public static function getHeading(): ?string
    {
        return __('wallet.transaction');
    }
    protected function getTableHeading(): string|Htmlable|null
    {
        return __('wallet.transaction');
    }

    public function getTableRecordTitle(Model $record): ?string
    {
        return __('wallet.transaction');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Transaction::query())
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label(__('string.date'))->dateTime(),
                Tables\Columns\TextColumn::make('processor_id')->label(__('wallet.processor_id'))->badge(),
                Tables\Columns\TextColumn::make('amount')->label(__('wallet.amount'))->money(),
                Tables\Columns\TextColumn::make('commission')->label(__('wallet.commission'))->money(),
                Tables\Columns\TextColumn::make('received')->label(__('wallet.received'))->money(),
                Tables\Columns\TextColumn::make('currency')->label(__('wallet.currency')),
                Tables\Columns\TextColumn::make('status')->label(__('wallet.status'))->badge(),
                Tables\Columns\TextColumn::make('from.name')->label(__('wallet.from')),
                Tables\Columns\TextColumn::make('to.name')->label(__('wallet.to')),

                Tables\Columns\TextColumn::make('meta')->label(__('wallet.meta')),
//                Tables\Columns\TextColumn::make('archived')->label(__('wallet.archived')),
            ]);
    }
}
