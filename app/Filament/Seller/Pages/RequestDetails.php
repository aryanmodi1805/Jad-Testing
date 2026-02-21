<?php

namespace App\Filament\Seller\Pages;

use App\Events\RefreshRequestEvent;
use App\Filament\Wallet\Actions\ChargeCreditAction;
use App\Filament\Wallet\Actions\PayRequestAction;
use App\Models\Message;
use App\Models\Request;
use App\Models\Response;
use App\Models\Seller;
use App\Settings\AppSettings;
use App\Settings\GeneralSettings;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Malzariey\FilamentDaterangepickerFilter\Enums\OpenDirection;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class RequestDetails extends Page
{
    use HasFiltersAction;
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 2 ;

    protected static string $view = 'filament.seller.pages.request-details';
    #[Url]
    public $requestId;
    public $currentRequestId;
    public $service;
    public $answers = [];
    public $customer;
    public $is_purchased = false;
    public $requests_data = [];
    public $seller;
    public $sellerId;
    public $selectedServices = [];
    public $selectedLocations = [];
    public $serviceOptions = [];
    public $locationOptions = [];
    public $selectAllServices = true;
    public $selectAllLocations = true;
    public $maximum_responses = 5;
    public $regular_customer = 5;

    public static function getNavigationLabel(): string
    {
        return __('seller.requests.plural');
    }

    public static function getModelLabel(): string
    {
        return __('seller.requests.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('seller.requests.single');
    }

    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    public function getTitle(): string|Htmlable
    {
        return __('seller.requests.plural');
    }

    public function mount(): void
    {

        $generalSettings = app(GeneralSettings::class);

        $this->maximum_responses = $generalSettings->maximum_responses;
        $this->regular_customer = $generalSettings->regular_customer_badge;

        $this->requests();

        if ($this->requestId) {
            $this->chooseRequest($this->requestId);
        } else {
            $this->chooseFirstRequest();
        }

    }

    #[Computed]
    public function requests()
    {
        $result = $this->getQuery()->with([
            'customer' => fn($query) => $query->withCount('requests'),
            'service'
        ])->orderBy('requests.created_at', 'desc')
            ->paginate(10);

        return $result;

    }

    public function getQuery(): Builder
    {
        $seller = Filament::auth()->user();

        return Request::query()
            ->select('*')
            ->withCount([
                'purchases as is_request_purchased' => fn($query) => $query->where('payable_id', $seller->id)
                    ->where('payable_type', Seller::class)
                    ->where('status', 1),
                'seller_responses as responses_count',
                'invites as is_invited' => fn($query) => $query->where('seller_id', $seller->id)
            ])
            ->withSum('customerAnswers as request_total_cost', 'val')
            ->canBeServedBySeller(Filament::auth()->user())
            ->orderBy('is_invited', 'desc')
            ->when(fn($query) => $query->having('responses_count', '<', $this->maximum_responses))
            ->when($this->filters['unique_requests'] ?? [], function ($query, $unique_requests) {
                if (in_array('first-respond', $unique_requests)) {
                    $query->having(fn($query) => $query->having('responses_count')->orHaving('responses_count', 0));
                }

                if (in_array('invite', $unique_requests)) {
                    $query->having('is_invited', 1);
                }
            })->when($this->filters['services'] ?? [], function ($query, $services) {
                $query->whereHas('service', function ($query) use ($services) {
                    $query->whereIn('services.id', $services);
                });
            })->when($this->filters['date_range'] ?? [], function ($query, $date_range) {
                $query->whereBetween('requests.created_at', [Carbon::createFromFormat('d/m/Y', explode(' - ', $date_range)[0])->startOfDay(),
                    Carbon::createFromFormat('d/m/Y', explode(' - ', $date_range)[1])->endOfDay()]);
            })->when($this->filters['order_by'] ?? [], function ($query, $order_by) {

                $query->orderBy($order_by, $order_by == 'created_at' ? 'desc' : 'asc');
            });

    }

    public function chooseRequest($requestId): void
    {
        $this->currentRequestId = $requestId;

    }

    public function chooseFirstRequest(): void
    {
        $this->currentRequestId = $this->requests->first()?->id;

    }

    public function filterAction()
    {
        return FilterAction::make()
            ->modalCancelActionLabel(__('labels.close'))
            ->badge(
                function () {
                    $count = count(array_filter(
                        $this->getFilters(),
                        fn($value, $key) => $key !== 'order_by' && !empty($value),
                        ARRAY_FILTER_USE_BOTH
                    ));
                    return $count > 0 ? $count : null;
                })
            ->icon('heroicon-o-funnel')
            ->color('primary')
            ->label(__('labels.filter'))
            ->outlined()
            ->extraModalFooterActions(
                fn() => [
                    Action::make('reset_filter')
                        ->color('secondary')
                        ->label(__('filament-tables::table.filters.actions.remove_all.label'))
                        ->action(function ($livewire, $action) {
                            $livewire->reset('filters');
                            $action->cancelParentActions();

                        }),

                ]
            )
            ->form([

                CheckboxList::make('unique_requests')
                    ->bulkToggleable()
                    ->options([
                        'first-respond' => __('labels.first-to-respond'),
                        'invite' => __('labels.customers_invitations'),
                    ])->label(__('labels.request-spotlights')),

                DateRangePicker::make('date_range')
                    ->label(__('labels.date_range'))
                    ->disableClear(false)
                    ->opens(fn() => app()->getLocale() == 'ar' ? OpenDirection::RIGHT : OpenDirection::LEFT),

                CheckboxList::make('services')
                    ->bulkToggleable()
                    ->options(auth('seller')->user()->services()->pluck('name', 'services.id'))
                    ->label(__('labels.services')),

                Select::make('order_by')
                    ->label(__('string.order_by'))
                    ->default('created_at')
                    ->options([
                        'request_total_cost' => __('subscriptions.price'),
                        'distance' => __('string.distance'),
                        'created_at' => __('columns.created_at'),
                    ])


            ])->after(fn() => $this->chooseFirstRequest());
    }

    /**
     * @return array|null
     */
    public function getFilters(): ?array
    {
        if ($this->filters) {
            $this->normalizeTableFilterValuesFromQueryString($this->filters);
        }
        return $this->filters ?? [];
    }

    public function cancelInvitationAction(): Action|null
    {
        return Action::make('cancelInvitation')
            ->label(__('string.cancel_invitation'))
            ->color('danger')
            ->requiresConfirmation()
            ->successNotificationTitle(__('string.invitation_cancelled'))
            ->visible(fn() => $this->currentRequest->is_invited > 0)
            ->action(function ($action) {
                if(empty($this->currentRequest)){
                    $action->cancel();
                    return;
                }
                $this->currentRequest->cancelInvitation();
                $this->chooseFirstRequest();
                $action->success();

            });
    }

    public function notInterestedAction(): Action|null
    {
        return Action::make('notInterested')
            ->label(__('string.not-interested'))
            ->color('primary')
            ->outlined()
            ->requiresConfirmation()
            ->action(function ($action) {
                if(empty($this->currentRequest)){
                    $action->cancel();
                    return;
                }
                $this->currentRequest->sellerNotInterested();
                $this->chooseFirstRequest();
                $action->success();

            });
    }

    public function contactAction(): Action|null
    {
        if ($this->currentRequest && $this->currentRequest->responses_count >= $this->maximum_responses) {
            return null;
        }

        $price = $this->currentRequest->getFinalPrice();

        $hasEnoughFunds = auth('seller')->user()->isEnoughFunds($price);


        if ($hasEnoughFunds) {

            $settings = app(AppSettings::class);

            // Check if seller has too many open/pending responses
            $maxOpenPending = $settings->max_open_pending_requests;
            $openPendingCount = Response::where('seller_id', auth('seller')->id())
                ->whereIn('status', [\App\Enums\ResponseStatus::Pending, \App\Enums\ResponseStatus::Invited])
                ->count();

            if ($openPendingCount >= $maxOpenPending) {
                return Action::make('contact')
                    ->label(__('string.contact_price', ['name' => $this->currentRequest->customer->name, 'price' => $price]))
                    ->icon('heroicon-o-user')
                    ->color('danger')
                    ->action(function () {
                        \Filament\Notifications\Notification::make()
                            ->title('Please complete your older requests first before connecting to new customers.')
                            ->warning()
                            ->send();
                    });
            }

            $maxRequests = $settings->maximum_requests_per_day;

            $todayResponsesCount = Response::where('seller_id', auth('seller')->id())
                ->whereDate('created_at', Carbon::today())
                ->count();

            if ($todayResponsesCount >= $maxRequests) {
                return Action::make('contact')
                    ->label(__('string.contact_price', ['name' => $this->currentRequest->customer->name, 'price' => $price]))
                    ->icon('heroicon-o-user')
                    ->color('danger')
                    ->action(function () {
                        \Filament\Notifications\Notification::make()
                            ->title('Maximum request per day are reached')
                            ->warning()
                            ->send();
                    });
            }

            return PayRequestAction::make('contact')
                ->label(__('string.contact_price', ['name' => $this->currentRequest->customer->name, 'price' => $price]))
                ->icon('heroicon-o-user')
                ->modalHeading(__('wallet.pay'))
                ->modalDescription(__('wallet.buy_request', ['name' => $this->currentRequest->customer->name]))
                ->record($this->currentRequest)
                ->after(fn() => $this->chooseFirstRequest())
                ->requiresConfirmation();
        } else {
            return ChargeCreditAction::make('contact')
                ->label(__('string.contact_price', ['name' => $this->currentRequest->customer->name, 'price' => $price]))
                ->icon('heroicon-o-user')
                ->modalHeading(__('wallet.top_up'))
                ->modalDescription(__('wallet.charge_credit_to_contact') . ' ' . $this->currentRequest->customer->name)
                ->record($this->currentRequest)
                ->requiresConfirmation();
        }
    }

    #[Computed]
    public function currentRequest()
    {
        return ($this->requests->firstWhere('id', $this->currentRequestId) ?? $this->requests->first())?->load([
            'customerAnswers',
        ]);

    }

    #[On('echo:reqRef,.App\\Events\\RefreshRequestEvent')]
    public function broadcastRefreshRequest($data): void
    {
        if ($data['type'] === RefreshRequestEvent::class) {

            /**
             * This will only be executed if the  auth Seller id  in sellers array
             */
            if ($data['sellerId'] &&  in_array(Filament::auth()->id(), $data['sellerId'],true) )
            {
                $this->requests();
                $this->currentRequest();
            }
            else
                $this->skipRender();

        }
        else
            $this->skipRender();
    }

}
