<?php

namespace App\Filament\Seller\Pages;

use App\Enums\CommunicationType;
use App\Enums\MessageType;
use App\Enums\ResponseStatus;
use App\Events\RefreshResponseEvent;
use App\Filament\Actions\ContactAction;
use App\Filament\Actions\PageChatAction;
use App\Filament\Actions\PageEstimateAction;
use App\Filament\Actions\RatingAction;
use App\Filament\Wallet\Actions\ChargeCreditAction;
use App\Filament\Wallet\Actions\PayRequestAction;
use App\Models\Estimate;
use App\Models\EstimateBase;
use App\Models\Message;
use App\Models\Response;
use App\Models\Seller;
use App\Services\ActivityService;
use App\Settings\GeneralSettings;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Malzariey\FilamentDaterangepickerFilter\Enums\OpenDirection;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class MyResponses extends Page implements HasForms, HasActions ,HasInfolists
{
    use InteractsWithForms;
    use InteractsWithActions;
    use HasFiltersAction;
    use InteractsWithInfolists;


    #[On('echo:reqRef,.App\\Events\\RefreshResponseEvent')]
    public function broadcastRefreshResponse($data): void
    {
        if ($data['type'] === RefreshResponseEvent::class) {

            /**
             * This will only be executed if the  auth Seller id  in sellers array
             */
            if ($data['sellerId'] &&  in_array(Filament::auth()->id(), $data['sellerId'],true) )
            {
                $this->responses();
            }
            else
                $this->skipRender();

        }
        else
            $this->skipRender();

    }

    /* @var Collection $responses  */

    public function persistsFiltersInSession(): bool
    {
        return false;
    }
    #[Url]
    public $responseId = null;
    public $selectedResponseId;

    public $maximum_responses = 5;
    public $regular_customer = 5;

    public function mount()
    {
        $generalSettings = app(GeneralSettings::class);

        $this->maximum_responses = $generalSettings->maximum_responses;
        $this->regular_customer = $generalSettings->regular_customer_badge;

        $this->responses();

        if($this->responseId) {
            $this->selectResponse($this->responseId);

        }else{
            $this->chooseFirstResponse();
            $this->filters['statuses'] =[ResponseStatus::Invited->value, ResponseStatus::Pending->value];

        }
    }

    public function chatAction(): Action|null
    {
        return PageChatAction::make('chat')
            ->record($this->selectedResponse);
    }
    public function filterAction()
    {
        return FilterAction::make()
            ->badge(function() {
                $filteredArray = array_filter($this->filters ??[],function($value , $key) {
                   return  $key != 'order_by' && filled($value);
                }  , ARRAY_FILTER_USE_BOTH);
                $count = count($filteredArray);
                return $count > 0 ? $count : null;
            })
            ->icon('heroicon-o-funnel')
            ->color('primary')
            ->label(__('labels.filter'))
            ->outlined()
            ->form([
                CheckboxList::make('statuses')
                    ->bulkToggleable()
                    ->options(ResponseStatus::getOptions())
                    ->label(__('string.status')),

                DateRangePicker::make('date_range')
                    ->label(__('labels.date_range'))
                    ->default(null)
                    ->disableClear(false)
                    ->opens(fn() => app()->getLocale() == 'ar' ? OpenDirection::RIGHT : OpenDirection::LEFT),

                CheckboxList::make('services')
                    ->bulkToggleable()
                    ->options(auth('seller')->user()->services()->pluck('name','services.id'))
                    ->label(__('labels.services')),

                Select::make('order_by')
                    ->label(__('string.order_by'))
                    ->default('created_at')
                    ->options([
                        'request_responses_count' => __('columns.responses_count'),
                        'created_at' => __('columns.created_at'),
                    ])


            ])->after(fn() => $this->chooseFirstResponse());
    }

    public function contactAction(): Action|null
    {
        if ($this->selectedResponse->status == ResponseStatus::Invited &&
            $this->selectedResponse->request->responses_count > $this->maximum_responses) {
            return null;
        }

        $price = $this->selectedResponse ? $this->selectedResponse->request->getFinalPrice() : 0;

        $hasEnoughFunds = auth('seller')->user()->isEnoughFunds($price);


        if($hasEnoughFunds){
            return PayRequestAction::make('contact')
                ->label(__('string.contact_price', ['name' => $this->selectedResponse->request->customer->name , 'price' => $price ]))
                ->icon('heroicon-o-user')
                ->modalHeading(__('wallet.pay'))
                ->modalDescription(__('wallet.buy_request',['name' => $this->selectedResponse->request->customer->name]))
                ->record($this->selectedResponse->request)
                ->requiresConfirmation();
        }else{
            return ChargeCreditAction::make('contact')
                ->label(__('string.contact_price', ['name' => $this->selectedResponse->request->customer->name , 'price' => $price ]))
                ->icon('heroicon-o-user')
                ->modalHeading(__('wallet.top_up'))
                ->modalDescription(__('wallet.charge_credit_to_contact'). ' ' . $this->selectedResponse->request->customer->name)
                ->record($this->selectedResponse->request)
                ->requiresConfirmation();
        }
    }

    public function cancelInvitationAction(): Action|null
    {
        return Action::make('cancelInvitation')
            ->label(__('string.cancel_invitation'))
            ->color('danger')
            ->requiresConfirmation()
            ->successNotificationTitle(__('string.invitation_cancelled'))
            ->visible(fn() => $this->selectedResponse->status == ResponseStatus::Invited)
            ->action(function($action){
                if(empty($this->selectedResponse)){
                    $action->cancel();
                    return;
                }
                $this->selectedResponse->request->cancelInvitation();
                $this->chooseFirstResponse();
                $action->success();
            });
    }
    public function contactActionGroup()
    {
        return ContactAction::make(record: $this->selectedResponse->request);

    }
    public function rateAction(): Action
    {
        return RatingAction::make('rate')
            ->rateable(fn()=> $this->selectedResponse->request)
            ->label(__('string.rate_customer'))
            ->rater(auth('seller')->user());
    }



    public function estimateInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->selectedResponse->estimate)
            ->schema([
                    Fieldset::make(__('responses.the_cost_estimate'))
                        ->visible(fn($record) => $record != null)
                        ->hiddenLabel()
                        ->schema([
                            TextEntry::make('amountPerBase')
                                ->columnSpanFull()
                                ->label(__('labels.amount'))
                                ->getStateUsing(fn($record) => $record?->amountPerBase)
                                ->icon('heroicon-o-banknotes')
                                ->iconColor('success'),

                            TextEntry::make('details')->columnSpanFull()
                                ->label(__('labels.description'))
                                ->hidden(fn($state) => is_null($state))
                                ->formatStateUsing(fn($state) => new HtmlString(nl2br(addBullets($state ?? ''))))
                        ]),

                TextEntry::make('status')
                    ->hiddenLabel()
                    ->visible(fn($record) => $record == null)
                    ->getStateUsing(fn() => __('string.not_available')),

            ]);
    }
    protected function getFormSchema(): array
    {
        return [
            TextInput::make('estimate.amount')
                ->label('Amount')
                ->numeric()
                ->required(),
            Select::make('estimate.estimate_base_id')
                ->label('Basis')
                ->options(EstimateBase::pluck('name', 'id'))
                ->required(),
            Textarea::make('estimate.details')
                ->label('Details')
                ->required()
                ->rows(5),
        ];
    }

    public function estimateAction(): Action
    {
        return PageEstimateAction::make()
            ->response(fn()=> $this->selectedResponse);
    }



    public function selectResponse($responseId)
    {
        $this->selectedResponseId = $responseId;
    }

    #[Computed]
    public function responses(){
        return $this->getQuery()->paginate(10);
    }

    #[Computed]
    public function selectedResponse(){
        return ($this->getQuery()->firstWhere('id', $this->selectedResponseId)??
            $this->responses->first())
            ?->load([
                    'request' => fn($query) => $query->withSum('customerAnswers as total_cost', 'val'),
                    'request.customer' => fn($query) => $query->withCount('requests'),
                    'request.customerAnswers',
            ]);

    }
    public function getQuery()
    {
        return Response::query()
            ->where('seller_id', auth('seller')->id())
            ->with([
                'seller' ,
                'estimate',
                'service',
                'request' => fn($query) => $query->withSum('customerAnswers as total_cost', 'val'),
                'request.customer' => fn($query) => $query->withCount('requests'),
            ])
            ->withCount('requestResponses')
            ->when($this->filters['statuses'] ?? [],
                fn($query, $statuses) => $query->whereIn('responses.status', $statuses))
            ->when($this->filters['date_range'] ?? [], function ($query, $date_range) {
                $query->whereBetween('responses.created_at', [Carbon::createFromFormat('d/m/Y',explode(' - ', $date_range)[0])->startOfDay(),
                    Carbon::createFromFormat('d/m/Y',explode(' - ', $date_range)[1])->endOfDay()]);
            })->when($this->filters['order_by'] ?? [], function ($query, $order_by) {
                $query->orderBy($order_by, $order_by == 'created_at' ? 'desc' : 'asc');
            })->when($this->filters['services'] ?? [], function ($query, $services) {
                $query->whereHas('service', function ($query) use ($services) {
                    $query->whereIn('services.id', $services);
                });
            })



            ->latest();
    }

    public function chooseFirstResponse(): void
    {
        $this->selectedResponseId = $this->responses->first()?->id;

    }

    protected static ?int $navigationSort = 3 ;
    public static function getNavigationLabel(): string
    {
        return __('seller.responses.plural');
    }

    public static function getModelLabel(): string
    {
        return __('seller.responses.single');
    }

    public function getTitle(): string|Htmlable
    {
        return __('seller.responses.plural');
    }

    public static function getPluralLabel(): ?string
    {
        return __('seller.responses.single');
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.seller.pages.my-responses';
}
