<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminNotificationResource\Pages;
use App\Models\AdminNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdminNotificationResource extends Resource
{
    protected static ?string $model = AdminNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    
    protected static ?string $navigationGroup = 'Communication';
    
    public static function getNavigationGroup(): ?string
    {
        return __('notification.admin_panel.communication');
    }
    
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('notification.admin_panel.sent_notifications');
    }

    public static function getModelLabel(): string
    {
        return __('notification.admin_panel.sent_notification');
    }

    public static function getPluralModelLabel(): string
    {
        return __('notification.admin_panel.sent_notifications');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('notification.admin_panel.notification_details'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('notification.admin_panel.title'))
                            ->disabled(),
                        Forms\Components\Textarea::make('body')
                            ->label(__('notification.admin_panel.message'))
                            ->disabled()
                            ->rows(3),
                        Forms\Components\Select::make('recipient_type')
                            ->label(__('notification.admin_panel.recipient_type'))
                            ->disabled()
                            ->options([
                                'all' => __('notification.admin_panel.all_users'),
                                'customers' => __('notification.admin_panel.customers'),
                                'sellers' => __('notification.admin_panel.sellers'),
                                'specific_customers' => __('notification.admin_panel.specific_customers'),
                                'specific_sellers' => __('notification.admin_panel.specific_sellers'),
                            ]),
                        Forms\Components\Toggle::make('is_database')
                            ->label(__('notification.admin_panel.database_notification'))
                            ->disabled(),
                        Forms\Components\Toggle::make('is_push')
                            ->label(__('notification.admin_panel.push_notification'))
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('notification.admin_panel.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                    
                Tables\Columns\TextColumn::make('body')
                    ->label(__('notification.admin_panel.message'))
                    ->limit(100)
                    ->tooltip(fn(AdminNotification $record): string => $record->body),
                    
                Tables\Columns\TextColumn::make('recipient_type')
                    ->label(__('notification.admin_panel.recipient_type'))
                    ->badge()
                    ->color(function (AdminNotification $record): string {
                        return match ($record->recipient_type) {
                            'all' => 'success',
                            'customers' => 'primary',
                            'sellers' => 'warning',
                            'specific_customers', 'specific_sellers' => 'info',
                            default => 'gray'
                        };
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'all' => __('notification.admin_panel.all_users'),
                        'customers' => __('notification.admin_panel.customers'),
                        'sellers' => __('notification.admin_panel.sellers'),
                        'specific_customers' => __('notification.admin_panel.specific_customers'),
                        'specific_sellers' => __('notification.admin_panel.specific_sellers'),
                        default => $state
                    }),
                    
                Tables\Columns\TextColumn::make('recipients_count')
                    ->label(__('notification.admin_panel.recipients_count'))
                    ->getStateUsing(function (AdminNotification $record): string {
                        $metadata = $record->metadata ?? [];
                        $sent = $metadata['sent_count'] ?? 0;
                        $total = $metadata['total_recipients'] ?? $record->recipients_count;
                        
                        if ($total > 0) {
                            return "{$sent}/{$total}";
                        }
                        
                        return (string) $record->recipients_count;
                    })
                    ->badge()
                    ->color(function (AdminNotification $record): string {
                        $metadata = $record->metadata ?? [];
                        $sent = $metadata['sent_count'] ?? 0;
                        $total = $metadata['total_recipients'] ?? $record->recipients_count;
                        
                        if ($total === 0) return 'gray';
                        if ($sent === $total) return 'success';
                        if ($sent > 0) return 'warning';
                        return 'danger';
                    }),
                    
                Tables\Columns\IconColumn::make('is_database')
                    ->label(__('notification.admin_panel.db'))
                    ->boolean(),
                    
                Tables\Columns\IconColumn::make('is_push')
                    ->label(__('notification.admin_panel.push'))
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('sentBy.name')
                    ->label(__('notification.admin_panel.sent_by'))
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('sent_at')
                    ->label(__('notification.admin_panel.sent_at'))
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('metadata')
                    ->label(__('notification.admin_panel.status'))
                    ->getStateUsing(function (AdminNotification $record): string {
                        $metadata = $record->metadata ?? [];
                        if (isset($metadata['processed_at'])) {
                            return 'processed';
                        }
                        if ($record->sent_at) {
                            return 'queued';
                        }
                        return 'draft';
                    })
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'processed' => __('notification.admin_panel.processed'),
                            'queued' => __('notification.admin_panel.queued'),
                            'draft' => __('notification.admin_panel.draft'),
                            default => $state
                        };
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'processed' => 'success',
                        'queued' => 'warning', 
                        'draft' => 'gray',
                        default => 'gray'
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('recipient_type')
                    ->label(__('notification.admin_panel.recipient_type'))
                    ->options([
                        'all' => __('notification.admin_panel.all_users'),
                        'customers' => __('notification.admin_panel.customers'),
                        'sellers' => __('notification.admin_panel.sellers'),
                        'specific_customers' => __('notification.admin_panel.specific_customers'),
                        'specific_sellers' => __('notification.admin_panel.specific_sellers'),
                    ]),
                    
                Tables\Filters\Filter::make('sent_at')
                    ->form([
                        Forms\Components\DatePicker::make('sent_from')
                            ->label(__('notification.admin_panel.sent_from')),
                        Forms\Components\DatePicker::make('sent_until')
                            ->label(__('notification.admin_panel.sent_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['sent_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('sent_at', '>=', $date),
                            )
                            ->when(
                                $data['sent_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('sent_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('notification.admin_panel.view')),
                Tables\Actions\DeleteAction::make()
                    ->label(__('notification.admin_panel.delete'))
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('notification.admin_panel.delete_selected'))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sent_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminNotifications::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            AdminNotificationResource\Widgets\NotificationStatsWidget::class,
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Use the main NotificationResource to create
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Notifications shouldn't be editable
    }
}
