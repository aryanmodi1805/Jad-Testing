<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use O21\LaravelWallet\Models\BalanceState;

class WalletBalanceStateTable extends BaseWidget
{
    protected static bool $isDiscovered = false;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $pollingInterval = '15';

    /**
     * @return string|null
     */
    public static function getHeading(): ?string
    {
        return __('wallet.balance_state');
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('wallet.balance_state');
    }

    public function getTableRecordTitle(Model $record): ?string
    {
        return __('wallet.balance_state');
    }
    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('created_at','desc')
            ->query(
                fn()=> BalanceState::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label(__('string.date'))->dateTime(),
                Tables\Columns\TextColumn::make('payable.name')->label(__('wallet.payable')) ,
                Tables\Columns\TextColumn::make('tx.processor_id')->label(__('wallet.processor_id'))->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'charge' => 'warning',
                        'deposit' => 'success',
                        'withdraw' => 'danger',
                        'transfer' => 'info',
                        default=> 'gray'
                    }),
                Tables\Columns\TextColumn::make('value')->label(__('wallet.balance')),
//                Tables\Columns\TextColumn::make('tx.received')->label(__('wallet.received')) ,
                Tables\Columns\TextColumn::make('tx.amount')->label(__('wallet.credits')),

                Tables\Columns\TextColumn::make('tx.commission')->label(__('wallet.commission')) ,

                Tables\Columns\TextColumn::make('tx.currency')
                    ->label(__('wallet.currency'))
                    ->formatStateUsing(fn ($state) => strtoupper($state) === 'CREDIT' ? __('wallet.credits') : $state),
                Tables\Columns\TextColumn::make('tx.status')->label(__('wallet.status'))->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'canceled' => 'danger',
                        'failed' => 'red',
                        default=> 'gray'
                    }),
                Tables\Columns\TextColumn::make('tx.from.name')->label(__('wallet.from')),
                Tables\Columns\TextColumn::make('tx.to.name')->label(__('wallet.to')),

                Tables\Columns\TextColumn::make('tx.meta')->label(__('wallet.meta'))
                    ->getStateUsing(function($record) {
                        if (!$record->tx?->meta) return " ";
                        
                        // Check for 'data' key (old format)
                        if (isset($record->tx->meta['data'])) {
                            return $record->tx->meta['data'];
                        }
                        
                        // Check for 'description' key (new format)
                        if (isset($record->tx->meta['description'])) {
                            $description = $record->tx->meta['description'];
                            
                            // Check if description contains UUID pattern and replace with readable names
                            if (preg_match('/#([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/i', $description, $matches)) {
                                $uuid = $matches[1];
                                $request = \App\Models\Request::find($uuid);
                                
                                if ($request) {
                                    // Replace UUID with service name and customer name
                                    $replacement = "{$request->service?->name} - {$request->customer?->name}";
                                    $description = str_replace("#$uuid", $replacement, $description);
                                    
                                    // Also update the prefix for clarity
                                    if (str_contains($description, 'Commission for Request')) {
                                        $description = str_replace('Commission for Request', 'Cash Payment Commission:', $description);
                                    } elseif (str_contains($description, 'Payment for Request')) {
                                        $description = str_replace('Payment for Request', 'Online Payment:', $description);
                                    }
                                }
                            }
                            
                            return $description;
                        }
                        
                        return " ";
                    }),
//                Tables\Columns\TextColumn::make('tx.archived')->label(__('wallet.archived')),



            ]);
    }
}
