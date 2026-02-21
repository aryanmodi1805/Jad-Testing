<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Customer;
use App\Models\Seller;
use App\Models\User;
use App\Notifications\AdminSentNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;

class NotificationResource extends Resource
{
    protected static ?string $model = DatabaseNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';
    
    protected static ?string $navigationGroup = 'Communication';

    public static function getNavigationGroup(): ?string
    {
        return __('notification.admin_panel.communication');
    }

    public static function getNavigationLabel(): string
    {
        return __('notification.admin_panel.notifications_management');
    }

    public static function getModelLabel(): string
    {
        return __('notification.admin_panel.notification');
    }

    public static function getPluralModelLabel(): string
    {
        return __('notification.admin_panel.notifications');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('notification.admin_panel.send_new_notification'))
                    ->description(__('notification.admin_panel.send_notifications_to_users'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('notification.admin_panel.notification_title'))
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\Textarea::make('body')
                            ->label(__('notification.admin_panel.notification_message'))
                            ->required()
                            ->rows(4),
                            
                        Forms\Components\Select::make('recipient_type')
                            ->label(__('notification.admin_panel.recipients'))
                            ->options([
                                'all' => __('notification.admin_panel.all_users_customers_sellers'),
                                'customers' => __('notification.admin_panel.all_customers'),
                                'sellers' => __('notification.admin_panel.all_sellers'),
                                'specific_customers' => __('notification.admin_panel.specific_customers'),
                                'specific_sellers' => __('notification.admin_panel.specific_sellers'),
                            ])
                            ->required()
                            ->reactive(),
                            
                        Forms\Components\Select::make('customer_ids')
                            ->label(__('notification.admin_panel.select_customers'))
                            ->multiple()
                            ->options(fn() => Customer::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->visible(fn(Forms\Get $get) => $get('recipient_type') === 'specific_customers'),
                            
                        Forms\Components\Select::make('seller_ids')
                            ->label(__('notification.admin_panel.select_sellers'))
                            ->multiple()
                            ->options(fn() => Seller::select('id', 'name', 'company_name')->get()
                                ->mapWithKeys(fn($seller) => [$seller->id => $seller->company_name ?? $seller->name])
                                ->toArray())
                            ->searchable()
                            ->visible(fn(Forms\Get $get) => $get('recipient_type') === 'specific_sellers'),
                            
                        Forms\Components\Section::make(__('notification.admin_panel.notification_types'))
                            ->schema([
                                Forms\Components\Checkbox::make('send_database')
                                    ->label(__('notification.admin_panel.database_notification'))
                                    ->helperText(__('notification.admin_panel.database_notification_help'))
                                    ->default(true),
                                    
                                Forms\Components\Checkbox::make('send_push')
                                    ->label(__('notification.admin_panel.push_notification'))
                                    ->helperText(__('notification.admin_panel.push_notification_help'))
                                    ->default(false),
                            ])
                            ->columns(2),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                DatabaseNotification::query()
                    ->with(['notifiable'])
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('data')
                    ->label(__('notification.admin_panel.title'))
                    ->getStateUsing(function (DatabaseNotification $record): string {
                        $data = $record->data;
                        return $data['title'] ?? $data['subject'] ?? __('notification.admin_panel.no_title');
                    })
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                    
                Tables\Columns\TextColumn::make('data')
                    ->label(__('notification.admin_panel.message'))
                    ->getStateUsing(function (DatabaseNotification $record): string {
                        $data = $record->data;
                        return $data['body'] ?? $data['message'] ?? __('notification.admin_panel.no_message');
                    })
                    ->limit(100)
                    ->tooltip(function (DatabaseNotification $record): string {
                        $data = $record->data;
                        return $data['body'] ?? $data['message'] ?? __('notification.admin_panel.no_message');
                    }),
                    
                Tables\Columns\TextColumn::make('notifiable_type')
                    ->label(__('notification.admin_panel.recipient_type'))
                    ->getStateUsing(function (DatabaseNotification $record): string {
                        return match($record->notifiable_type) {
                            'App\\Models\\Customer' => 'customer',
                            'App\\Models\\Seller' => 'seller',
                            'App\\Models\\User' => 'admin_user',
                            default => 'other'
                        };
                    })
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'customer' => __('notification.admin_panel.customer'),
                            'seller' => __('notification.admin_panel.seller'),
                            'admin_user' => __('notification.admin_panel.admin_user'),
                            default => $state
                        };
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'customer' => 'success',
                        'seller' => 'warning',
                        'admin_user' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('notifiable.name')
                    ->label(__('notification.admin_panel.recipient'))
                    ->getStateUsing(function (DatabaseNotification $record): string {
                        if (!$record->notifiable) {
                            return __('notification.admin_panel.user_deleted');
                        }
                        
                        if ($record->notifiable instanceof Seller) {
                            return $record->notifiable->company_name ?? $record->notifiable->name;
                        }
                        
                        return $record->notifiable->name ?? __('notification.admin_panel.unknown');
                    })
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('read_at')
                    ->label(__('notification.admin_panel.read'))
                    ->boolean()
                    ->getStateUsing(fn(DatabaseNotification $record) => !is_null($record->read_at))
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('type')
                    ->label(__('notification.admin_panel.notification_type'))
                    ->getStateUsing(function (DatabaseNotification $record): string {
                        return class_basename($record->type);
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('notification.admin_panel.sent_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('read_at')
                    ->label(__('notification.admin_panel.read_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('notifiable_type')
                    ->label(__('notification.admin_panel.recipient_type'))
                    ->options([
                        'App\\Models\\Customer' => __('notification.admin_panel.customers'),
                        'App\\Models\\Seller' => __('notification.admin_panel.sellers'),
                        'App\\Models\\User' => __('notification.admin_panel.admin_user'),
                    ]),
                    
                Tables\Filters\SelectFilter::make('read_status')
                    ->label(__('notification.admin_panel.read_status'))
                    ->options([
                        'read' => __('notification.admin_panel.read'),
                        'unread' => __('notification.admin_panel.unread'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] === 'read',
                            fn (Builder $query): Builder => $query->whereNotNull('read_at'),
                        )->when(
                            $data['value'] === 'unread',
                            fn (Builder $query): Builder => $query->whereNull('read_at'),
                        );
                    }),
                    
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('notification.admin_panel.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('notification.admin_panel.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading(__('notification.admin_panel.notification_details_modal'))
                    ->modalContent(function (DatabaseNotification $record): \Illuminate\Contracts\View\View {
                        return view('filament.resources.notification.view', ['record' => $record]);
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->label(__('notification.admin_panel.delete'))
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('notification.admin_panel.delete_selected'))
                        ->requiresConfirmation(),
                        
                    Tables\Actions\BulkAction::make('mark_as_read')
                        ->label(__('notification.admin_panel.mark_as_read'))
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function (DatabaseNotification $record) {
                                if (is_null($record->read_at)) {
                                    $record->markAsRead();
                                }
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Notifications shouldn't be editable
    }
}
