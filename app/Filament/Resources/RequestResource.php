<?php

namespace App\Filament\Resources;

use App\Enums\RequestStatus;
use App\Filament\Resources\RequestResource\Actions\ChangeStatusBulkAction;
use App\Filament\Resources\RequestResource\Pages;
use App\Filament\Resources\RequestResource\RelationManagers\CustomerAnswersRelationManager;
use App\Filament\Resources\RequestResource\RelationManagers\ResponsesRelationManager;
use App\Models\Request;
use App\Models\Response;
use Cheesegrits\FilamentGoogleMaps\Infolists\MapEntry;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\View;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = -10;

    protected static bool $isScopedToTenant = true;

    protected array $options = [];

    public static function getNavigationGroup(): ?string
    {
        return __('nav.services');
    }

    public static function getNavigationLabel(): string
    {
        return __('services.requests.plural');
    }

    public static function getModelLabel(): string
    {
        return __('services.requests.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('services.requests.plural');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__('labels.overview'))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                            'xl' => 3,
                        ])->schema([
                            TextEntry::make('service.name')
                                ->label(__('columns.service_name'))
                                ->icon('heroicon-o-briefcase')
                                ->iconColor('primary'),

                            TextEntry::make('status')
                                ->label(__('columns.status'))
                                ->badge(),

                            TextEntry::make('customer_answers_sum_val')
                                ->label(__('columns.total_cost'))
                                ->icon('heroicon-o-banknotes')
                                ->formatStateUsing(static function ($state): string {
                                    if (filled($state)) {
                                        return number_format((float) $state, 2).' '.__('wallet.credits');
                                    }

                                    return __('labels.na');
                                }),

                            TextEntry::make('responses_count')
                                ->label(__('columns.responses_count'))
                                ->icon('heroicon-o-user-group')
                                ->formatStateUsing(static fn ($state): string => (string) ($state ?? 0)),

                            TextEntry::make('created_at')
                                ->label(__('columns.created_at'))
                                ->dateTime()
                                ->icon('heroicon-o-calendar-days'),
                        ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make(__('labels.customer_details'))
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                            'xl' => 3,
                        ])->schema([
                            TextEntry::make('customer.name')
                                ->label(__('columns.customer_name'))
                                ->icon('heroicon-o-user-circle')
                                ->iconColor('primary'),

                            TextEntry::make('customer.email')
                                ->label(__('columns.email'))
                                ->icon('heroicon-o-envelope')
                                ->placeholder(__('labels.na')),

                            TextEntry::make('customer.phone')
                                ->label(__('columns.phone'))
                                ->icon('heroicon-o-phone')
                                ->placeholder(__('labels.na')),

                            TextEntry::make('customer.rate')
                                ->label(__('seller.rate.single'))
                                ->icon('heroicon-o-star')
                                ->formatStateUsing(static function ($state): string {
                                    return filled($state)
                                        ? number_format((float) $state, 1).' / 5'
                                        : __('labels.na');
                                }),

                            TextEntry::make('customer.rate_count')
                                ->label(__('seller.rate.ratings'))
                                ->icon('heroicon-o-chart-bar')
                                ->formatStateUsing(static fn ($state): string => (string) ($state ?? 0)),

                            IconEntry::make('customer.isPhoneVerified')
                                ->label(__('string.verified-phone'))
                                ->boolean()
                                ->trueIcon('heroicon-o-check-circle')
                                ->trueColor('success')
                                ->falseIcon('heroicon-o-x-circle'),

                            TextEntry::make('customer.requests_count')
                                ->label(__('services.requests.count'))
                                ->icon('heroicon-o-clipboard-document-check')
                                ->formatStateUsing(static fn ($state): string => (string) ($state ?? 0)),

                            TextEntry::make('customer.country.name')
                                ->label(__('columns.country_id'))
                                ->icon('heroicon-o-globe-asia-australia')
                                ->placeholder(__('labels.na')),
                        ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make(__('string.request_location'))
                    ->icon('heroicon-o-map')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])->schema([
                            TextEntry::make('location_name')
                                ->label(__('columns.location_name'))
                                ->icon('heroicon-o-map-pin')
                                ->placeholder(__('labels.na')),

                            TextEntry::make('location_type')
                                ->label(__('columns.location_type'))
                                ->icon('heroicon-o-building-office')
                                ->placeholder(__('labels.na')),

                            TextEntry::make('latitude')
                                ->label(__('columns.latitude'))
                                ->icon('heroicon-o-arrow-long-up')
                                ->placeholder(__('labels.na')),

                            TextEntry::make('longitude')
                                ->label(__('columns.longitude'))
                                ->icon('heroicon-o-arrow-long-right')
                                ->placeholder(__('labels.na')),
                        ]),

                        MapEntry::make('location')
                            ->label(__('string.request_location'))
                            ->height('20rem')
                            ->visible(fn (Request $record): bool => filled($record->latitude) && filled($record->longitude)),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make(__('services.customer_answers.plural'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        View::make('filament.resources.request-resource.components.customer-answers')
                            ->label(__('services.customer_answers.plural')),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make(__('seller.responses.plural'))
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        RepeatableEntry::make('responses')
                            ->label(__('seller.responses.plural'))
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make([
                                            'default' => 1,
                                            'md' => 2,
                                            'xl' => 3,
                                        ])->schema([
                                            TextEntry::make('seller.name')
                                                ->label(__('columns.seller_name'))
                                                ->icon('heroicon-o-user')
                                                ->url(fn (Response $record) => SellerResource::getUrl('view', ['record' => $record->seller_id]))
                                                ->openUrlInNewTab(),

                                            TextEntry::make('status')
                                                ->label(__('labels.status'))
                                                ->badge(),

                                            TextEntry::make('created_at')
                                                ->label(__('columns.created_at'))
                                                ->dateTime()
                                                ->icon('heroicon-o-calendar'),

                                            IconEntry::make('is_approved')
                                                ->label(__('columns.approved'))
                                                ->boolean(),

                                            TextEntry::make('rate')
                                                ->label(__('seller.rate.single'))
                                                ->icon('heroicon-o-star')
                                                ->placeholder(__('labels.na')),
                                        ]),

                                        TextEntry::make('notes')
                                            ->label(__('labels.notes'))
                                            ->placeholder(__('labels.na'))
                                            ->icon('heroicon-o-document-text')
                                            ->columnSpanFull(),

                                        TextEntry::make('estimate_amount')
                                            ->label(__('labels.amount'))
                                            ->icon('heroicon-o-banknotes')
                                            ->state(fn (Response $record) => $record->estimate?->amountPerBase)
                                            ->visible(fn (Response $record): bool => filled($record->estimate?->amountPerBase)),

                                        TextEntry::make('estimate_details')
                                            ->label(__('labels.description'))
                                            ->state(fn (Response $record) => $record->estimate?->details)
                                            ->formatStateUsing(static fn ($state) => $state ? new HtmlString(nl2br(e($state))) : null)
                                            ->visible(fn (Response $record): bool => filled($record->estimate?->details))
                                            ->columnSpanFull(),
                                    ])
                                    ->extraAttributes([
                                        'class' => 'bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-800 rounded-xl p-4',
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->contained(false)
                            ->columnSpanFull()
                            ->visible(fn (Request $record): bool => $record->responses->isNotEmpty()),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ])
            ->columns(1);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->label(__('columns.customer_id')),

                Select::make('service_id')
                    ->relationship('service', 'name')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->label(__('columns.service_id')),

                TextInput::make('location_name')
                    ->label(__('columns.location_name')),

                TextInput::make('latitude')
                    ->numeric()
                    ->label(__('columns.latitude')),

                TextInput::make('longitude')
                    ->numeric()
                    ->label(__('columns.longitude')),

                TextInput::make('location_type')
                    ->required()
                    ->label(__('columns.location_type')),

                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->label(__('columns.country_id')),

            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading(__('string.requests.empty'))
            ->columns([
                TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label(__('columns.customer_name')),

                TextColumn::make('service.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label(__('columns.service_name')),

                SelectColumn::make('status')
                    ->options(
                        RequestStatus::class
                    )
                    ->width('200px')
                    ->toggleable()
                    ->selectablePlaceholder(false)
                    ->extraAttributes([
                        'style' => 'min-width: 120px',
                    ])
                    ->label(__('columns.status')),

                TextColumn::make('invoice_amount')
                    ->label(__('columns.total_cost'))
                    ->state(function (Request $record) {
                        // Find the first response that has an estimate (invoice)
                        // Responses are already eager loaded and ordered by latest in getEloquentQuery
                        $responseWithInvoice = $record->responses->first(function ($response) {
                             return $response->estimate && $response->estimate->amount > 0;
                        });

                        if ($responseWithInvoice && $responseWithInvoice->estimate) {
                             return $responseWithInvoice->estimate->amount . ' ' . getCurrentTenant()?->currency?->code ?? 'SAR';
                        }
                        
                        return 'invoice not created';
                    })
                    ->toggleable(),

                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->state(function (Request $record) {
                        $hiredResponse = $record->responses->first(fn($r) => $r->status === \App\Enums\ResponseStatus::Hired);
                        
                        if (!$hiredResponse) return '-';

                        $seller = $hiredResponse->seller;
                        if (!$seller) return '-';

                        $customerName = $record->customer->name ?? '';
                        
                        // 1. Try to find by request_id in metadata first (best for new transactions)
                        $transaction = \O21\LaravelWallet\Models\Transaction::where(function ($query) use ($seller) {
                                $query->where(function ($q) use ($seller) {
                                    $q->where('to_id', $seller->id)
                                      ->where('to_type', get_class($seller));
                                })->orWhere(function ($q) use ($seller) {
                                    $q->where('from_id', $seller->id)
                                      ->where('from_type', get_class($seller));
                                });
                            })
                            ->where('meta->request_id', $record->id)
                            ->latest()
                            ->first();

                        // 2. Fallback to searching by description with all possible service name translations
                        if (!$transaction) {
                            $serviceModel = $record->service;
                            $serviceNames = [];
                            if ($serviceModel) {
                                // Get all translations for the service name
                                $serviceNames = array_values($serviceModel->getTranslations('name'));
                            }

                            $transaction = \O21\LaravelWallet\Models\Transaction::where(function ($query) use ($seller) {
                                    $query->where(function ($q) use ($seller) {
                                        $q->where('to_id', $seller->id)
                                          ->where('to_type', get_class($seller));
                                    })->orWhere(function ($q) use ($seller) {
                                        $q->where('from_id', $seller->id)
                                          ->where('from_type', get_class($seller));
                                    });
                                })
                                ->where(function ($query) use ($serviceNames, $customerName) {
                                    foreach ($serviceNames as $name) {
                                        $query->orWhere(function ($q) use ($name, $customerName) {
                                            $q->where('meta->description', 'like', "%{$name}%")
                                              ->where('meta->description', 'like', "%{$customerName}%");
                                        });
                                    }
                                })
                                ->latest()
                                ->first();
                        }

                        if ($transaction) {
                            $desc = $transaction->meta['description'] ?? '';
                            if (str_contains($desc, 'Online Payment')) return 'Online';
                            if (str_contains($desc, 'Cash Payment')) return 'Cash';
                        }
                        
                        return 'not found'; 
                    }),

                TextColumn::make('responses_count')
                    ->label(__('columns.responses_count'))
                    ->toggleable(),

                TextColumn::make('location_name')
                    ->wrap()
                    ->width('200px')
                    ->toggleable()
                    ->label(__('columns.location_name')),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable()
                    ->sortable()
                    ->label(__('columns.created_at')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('columns.status'))
                    ->options(
                        RequestStatus::class
                    )
                    ->multiple(),

                SelectFilter::make('service')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload()
                    ->label(__('columns.service_name')),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label(__('columns.from')),
                        DatePicker::make('created_until')
                            ->label(__('columns.until')),
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
                DeleteAction::make(),

            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ChangeStatusBulkAction::make(),
                ]),
            ]);
    }

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
            'create' => Pages\CreateRequest::route('/create'),
            'view' => Pages\ViewRequest::route('/{record}'),

        ];
    }

    public static function getRelations(): array
    {
        return [
            CustomerAnswersRelationManager::class,
            ResponsesRelationManager::class,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['customer', 'service']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['customer.name', 'service.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];

        if ($record->customer) {
            $details['Customer'] = $record->customer->name;
        }

        if ($record->service) {
            $details['Service'] = $record->service->name;
        }

        return $details;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->latest()
            ->withSum('customerAnswers', 'val')
            ->withCount('responses')
            ->with([
                'service',
                'country',
                'customer' => fn ($query) => $query->withCount('requests')->with('country'),
                'customerAnswers' => fn ($query) => $query->orderBy('question_sort'),
                'responses' => fn ($query) => $query
                    ->with(['seller', 'estimate'])
                    ->latest(),
            ]);
    }

    public function getTitle(): string|Htmlable
    {
        return __('services.requests.single');
    }
}
