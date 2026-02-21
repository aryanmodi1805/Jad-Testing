<?php

namespace App\Filament\Customer\Resources;

use App\Enums\RequestStatus;
use App\Enums\ResponseStatus;
use App\Events\RefreshRequestEvent;
use App\Filament\Actions\ApproveResponseAction;
use App\Filament\Actions\InfoListChatAction;
use App\Filament\Actions\RatingInfoListAction;
use App\Filament\Actions\SellerProfileAction;
use App\Filament\Customer\Resources\RequestResource\Pages;
use App\Livewire\ShowMatchingSellers;
use App\Models\Request;
use App\Models\Response;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\View;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Grid as TableGrid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class RequestResource extends Resource implements HasInfolists
{
    use InteractsWithInfolists;
    protected static bool $shouldSkipAuthorization = true;

    protected static ?string $model = Request::class;

    protected static ?string $slug = '/';
    protected static string $routePath = '/';

    public static function getRoutePath(): string
    {
        return static::$routePath;
    }

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return __('services.requests.plural');
    }

    public static function getModelLabel(): string
    {
        return __('services.requests.single');
    }

    public function getTitle(): string|Htmlable
    {
        return __('services.requests.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('services.requests.plural');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Tabs::make(__('labels.details'))
                ->columnSpanFull()
                ->tabs([
                    Tab::make(__('responses.response_title'))
                        ->badge(fn($record) => $record->responses_count ?? $record->responses?->count() )
                        ->schema([
                            TextEntry::make('rejected_message')
                                ->hiddenLabel()
                                ->visible(fn($record) => $record->status === RequestStatus::Rejected)
                                ->state(__('labels.request_rejected_message'))
                                ->extraAttributes(['class' => 'text-red-500 bg-red-100 p-2 rounded']),

                            TextEntry::make('pending_message')
                                ->hiddenLabel()
                                ->visible(fn($record) => $record->status === RequestStatus::Pending)
                                ->state(__('labels.request_pending_message'))
                                ->extraAttributes(['class' => 'text-yellow-500 bg-yellow-100 p-2 rounded']),
                            Fieldset::make(__('labels.professionals_have_responded'))->schema(fn($record) => [
                                View::make('components.progress-bar')->viewData([
                                    'current' => $record->responses_count,
                                    'total' => getMaxResponses(),
                                ]),
                            ])->visible(fn($record) => !in_array($record->status, [RequestStatus::Pending, RequestStatus::Rejected])),

                            RepeatableEntry::make('responses')
                                ->hiddenLabel()
                                ->schema([
                                    Section::make()
                                        ->icon('heroicon-o-user')
                                        ->extraAttributes([
                                            'class'=>'custom-section',
                                        ])
                                        ->heading(fn(Response $record) => new HtmlString(
                                            view('components.seller-header', [
                                                'name' => filled($record->seller->company_name) ? $record->seller->company_name : $record->seller->name,
                                                'rating' => $record->seller->rate ?? 0
                                            ])))
                                        ->iconColor('primary')
                                        ->schema([
                                            \Filament\Infolists\Components\Split::make([
                                                Grid::make(['default' => 1,
                                                    'sm' => 2,
                                                    'md' => 3,
                                                    'lg' => 4,
                                                    'xl' => 4,
                                                    '2xl' => 4,])
                                                    ->schema([
                                                        TextEntry::make('status')->grow(false)
                                                            ->label(__('labels.status'))
                                                            ->badge()->inlineLabel()
                                                            ->color(fn($state) => $state instanceof ResponseStatus ? $state->getColor() : 'gray')
                                                            ->icon(fn($state) => $state instanceof ResponseStatus ? $state->getIcon() : 'heroicon-o-question-mark-circle'),
                                                        TextEntry::make('created_at')
                                                            ->label(__('labels.date'))
                                                            ->inlineLabel()
                                                            ->dateTime(),

                                                        TextEntry::make('notes')
                                                            ->grow(false)
                                                            ->label(__('labels.notes'))
                                                            ->placeholder(__('labels.na'))
                                                            ->inlineLabel()
                                                            ->icon('heroicon-o-document-text')
                                                            ->iconColor('info')
                                                    ])->grow(false),
                                                Actions::make([
                                                    SellerProfileAction::make(),
                                                    InfoListChatAction::make(),
                                                    ApproveResponseAction::make('approve')
                                                        ->response(fn($record) => $record)
                                                        ->label(__('labels.approve'))
                                                        ->requiresConfirmation()
                                                        ->hidden(fn(Response $record) => $record->status != ResponseStatus::Pending || !in_array($record->request->status, [RequestStatus::Open, RequestStatus::Booking]) || $record->isBlocked(Filament::auth()->user(), $record->seller)),

                                                    Action::make('cancel-invitation')->color('danger')->label(__('string.cancel_invitation'))
                                                        ->requiresConfirmation()
                                                        ->visible(fn(Response $record) => $record->status == ResponseStatus::Invited)
                                                        ->action(function (Response $record) {
                                                            $seller_id = $record->seller_id ;
                                                            $record->delete();
                                                            broadcast(new RefreshRequestEvent([$seller_id]));
                                                        })

                                                        ->after(fn($livewire) => $livewire->dispatch('RefreshMatchingSellers')),

                                                    RatingInfoListAction::make()
                                                        ->rater(auth('customer')->user())
                                                        ->rateable(fn($record) => $record)
                                                        ->modalHeading(__('seller.rate.rate_seller'))
                                                        ->visible(fn($record) => $record->status == ResponseStatus::Hired)
                                                        ->label(__('seller.rate.rate_seller')),
                                                ])->columns(1)->alignEnd()->extraAttributes([
                                                    'style' => isDesktop() ? 'max-width: 200px; margin-inline-start:auto;' : ''
                                                ])
                                            ])->from('sm'),
                                            Grid::make()
                                                ->schema([
                                                    Fieldset::make(__('responses.the_cost_estimate'))->columnSpanFull()
                                                        ->visible(fn(Response $record) => $record->estimate_count > 0)
                                                        ->schema([
                                                            TextEntry::make('totalAmount')
                                                                ->columnSpanFull()
                                                                ->label(__('labels.amount'))
                                                                ->getStateUsing(fn(Response $record) => $record?->estimate?->amountPerBase)
                                                                ->icon('heroicon-o-banknotes')
                                                                ->iconColor('success'),

                                                            TextEntry::make('description')->columnSpanFull()
                                                                ->label(__('labels.description'))
                                                                ->hidden(fn($state) => is_null($state))
                                                                ->getStateUsing(fn(Response $record) => new HtmlString(nl2br(addBullets($record?->estimate?->details ?? '')))),
                                                        ]),
                                                ]),
                                        ]),
                                ])
                                ->hidden(fn($record) => in_array($record->status, [RequestStatus::Pending, RequestStatus::Rejected]))
                                ->columnSpanFull()
                                ->columns(2)
                                ->contained(false),
                        ]),

                    Tab::make(__('localize.answer.my_answers'))
                        ->schema([
                            View::make('filament.customer.components.request_answers')->label(__('localize.answer.my_answers'))
                        ]),

                    Tab::make(__('labels.your_match'))
                        ->visible(fn($record) => in_array($record->status, [RequestStatus::Open, RequestStatus::Booking]))
                        ->schema([
                            TextEntry::make('rejected_message')
                                ->hiddenLabel()
                                ->visible(fn($record) => $record->status === RequestStatus::Rejected)
                                ->state(__('labels.request_rejected_message'))
                                ->extraAttributes(['class' => 'text-red-500 bg-red-100 p-2 rounded']),

                            TextEntry::make('pending_message')
                                ->hiddenLabel()
                                ->visible(fn($record) => $record->status === RequestStatus::Pending)
                                ->state(__('labels.request_pending_message'))
                                ->extraAttributes(['class' => 'text-yellow-500 bg-yellow-100 p-2 rounded']),

                            Livewire::make(ShowMatchingSellers::class)
                                ->hidden(fn($record) => $record->status === RequestStatus::Pending || $record->status === RequestStatus::Rejected)

                        ])
                ]),
        ]);
    }

    private static function getCommonSchema(): array
    {
        return [
            TextEntry::make('question.label')
                ->label(__('labels.question'))
                ->icon('heroicon-o-question-mark-circle')
                ->iconColor('info')
            ,

            TextEntry::make('answer.label')
                ->label(__('labels.answer'))
                ->icon('heroicon-o-chat-bubble-left')
                ->iconColor('success')
                ->getStateUsing(function ($record) {
                    if (!$record->is_custom && !is_null($record->text_answer)) {
                        return $record->text_answer;
                    }
                    return $record->is_custom ? $record->custom_answer : ($record->answer->label ?? __('labels.not_available'));
                }),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('service_id')
                ->relationship('service', 'name', function ($query) {
                    $query->where('country_id', getCountryId());
                })
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TableGrid::make([
                    'md' => 1,
                    'xl' => 1,
                ])
                    ->schema([
                        Stack::make([
                            TextColumn::make('service.name')
                                ->label('Service Name')
                                ->weight(FontWeight::Bold)
                                ->searchable()
                                ->extraAttributes(['class' => 'text-lg']),
                        ]),

                        Split::make([
                            TextColumn::make('status')
                                ->label('Request Status')
                                ->searchable()
                                ->badge()
                                ->extraAttributes(['class' => 'text-sm text-gray-500']),

                            TextColumn::make('responses_count')
                                ->getStateUsing(fn($record) => $record->responses->count() . ' ' . __('responses.SReplies'))
                                ->label('Response Count')
                                ->searchable()
                                ->icon('heroicon-o-chat-bubble-bottom-center-text')
                                ->extraAttributes(['class' => 'text-sm text-gray-500']),

                            TextColumn::make('created_at')
                                ->label('Created At')
                                ->since()
                                ->icon('heroicon-o-clock')
                                ->extraAttributes(['class' => 'text-sm text-gray-500']),
                        ])->from('md'), // Columns will stack on mobile
                    ]),
            ])
            ->contentGrid([
                'md' => 3,
                'xl' => 3,
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
            'view' => Pages\ViewRequest::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('customer_id', auth('customer')->id())
            ->latest()
            ->withCount('responses')
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
