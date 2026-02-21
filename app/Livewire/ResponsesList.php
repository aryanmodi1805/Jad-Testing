<?php

namespace App\Livewire;

use App\Enums\CommunicationType;
use App\Enums\MessageType;
use App\Enums\ResponseStatus;
use App\Filament\Actions\PageChatAction;
use App\Filament\Actions\PageEstimateAction;
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
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Livewire\Component;

class ResponsesList extends Component implements HasForms , HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;


    public $responses;
    public $selectedResponseId;

    public $selectedIsPurchased;
    public $selectedResponse;
    public $modalConfig = [];
    public $estimate = [
        'amount' => '',
        'estimate_base_id' => '',
        'details' => '',
    ];
    public $basisOptions = [];
    public $hasEstimate = false;
    public $statusFilter = null;
    public $status;

    public function mount($responseId = null)
    {
        $this->initializeForm();
        $this->loadResponses($responseId);
    }

    protected function initializeForm()
    {
        $settings = EstimateBase::pluck('name', 'id');
        $this->basisOptions = $settings;
        $this->form->fill([]);
    }

    public function chatAction(): Action|null
    {
        return PageChatAction::make('chat')
            ->record($this->selectedResponse)
            ->visible(fn() => $this->selectedIsPurchased);
    }
    public function payRequestAction(): Action|null
    {
        $maximum_responses = app(GeneralSettings::class)->maximum_responses;

        if ($this->selectedResponse && $this->selectedResponse->request->responses()->count() > $maximum_responses) {
            return null;
        }

        $this->selectedIsPurchased = auth('seller')->user()->is_purchased($this->selectedResponse->request);
        $price = $this->selectedResponse ? $this->selectedResponse->request->getFinalPrice() : 0;

        if (auth('seller')->user()->isEnoughFunds($price)) {
            return PayRequestAction::make('payRequest')
                ->record($this->selectedResponse->request)
                ->visible(fn() => !$this->selectedIsPurchased)
                ->requiresConfirmation();
        }

        return ChargeCreditAction::make('payRequest')
            ->record($this->selectedResponse->request)
            ->visible(fn() => !$this->is_purchased)
            ->requiresConfirmation();
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
                ->options($this->basisOptions)
                ->required(),
            Textarea::make('estimate.details')
                ->label('Details')
                ->rows(5),
        ];
    }

    protected function getFormStatusSchema(): array
    {
        return [
            Select::make('status')
                ->label('Current Status')
                ->options($this->getStatusOptions())
                ->required()
                ->reactive()
                ->afterStateUpdated(fn ($state) => $this->saveStatus($state)),
        ];
    }

    protected function getStatusOptions(): array
    {
        return collect(ResponseStatus::cases())->mapWithKeys(fn ($status) => [
            $status->value => $status->getLabel()
        ])->toArray();
    }

    public function estimateAction() : Action
    {
        return PageEstimateAction::make()
            ->response($this->selectedResponse);

    }
    public function saveEstimate(): void
    {
        $validatedData = $this->form->getState();

        $estimate = Estimate::updateOrCreate(
            ['response_id' => $this->selectedResponseId],
            [
                'amount' => $validatedData['estimate']['amount'],
                'estimate_base_id' => $validatedData['estimate']['estimate_base_id'],
                'details' => $validatedData['estimate']['details']??"",
            ]
        );

        Message::create([
            'response_id' => $this->selectedResponseId,
            'sender_id' => auth('seller')->id(),
            'sender_type' => Seller::class,
            'message' => 'Estimate updated',
            'payload' => [
                'type' => MessageType::Estimate,
                'data' => $estimate->load('estimateBase')
            ],
            'type_type' => Estimate::class,

        ]);

        $this->dispatch('close-modal', id: 'estimate-modal');
    }

    public function saveStatus($state)
    {
        $this->status = $state;

        $this->validate([
            'status' => 'required',
        ]);

        app(ActivityService::class)->handleStatusUpdate($this->selectedResponse->id, $this->status);

        if ($this->selectedResponse) {
            $this->selectedResponse->update(['status' => $this->status]);
            Notification::make()
                ->success()
                ->title('success')
                ->send();

            $this->mount($this->selectedResponseId);
        }
    }

    public function selectResponse($responseId)
    {

        $this->selectedResponseId = $responseId;
        $this->selectedResponse = Response::with(['seller', 'activities' => fn ($q) => $q->orderBy('created_at', 'desc')])->find($responseId);

        $this->selectedIsPurchased = auth('seller')->user()->is_purchased($this->selectedResponse->request);

        $estimate = Estimate::with('estimateBase')->where('response_id', $responseId)->first();
        $this->hasEstimate = !is_null($estimate);

        $this->form->fill([
            'estimate.amount' => $estimate->amount ?? '',
            'estimate.estimate_base_id' => $estimate->estimate_base_id ?? '',
            'estimate.estimate_name' => $estimate->estimateBase->name ?? '',
            'estimate.details' => $estimate->details ?? '',
        ]);

        $this->status = $this->selectedResponse->status->value;
    }

    public function showModal($type): void
    {
        $typeEnum = CommunicationType::from($type);
        $name = $this->selectedResponse->request?->customer?->name ?? '';
        $number = $this->selectedResponse->request?->customer?->phone ?? '';

        $this->modalConfig = [
            'type' => $type,
            'statuses' => $typeEnum->statuses(),
            'colors' => $typeEnum->colors(),
            'icon' => $typeEnum->icon(),
            'headerInfo' => $typeEnum->headerInfo($name, $number),
        ];

        $this->dispatch('open-modal', id: 'communication-modal');
    }

    public function updateCommunicationStatus($communicationType, $status): void
    {
        if ($this->selectedResponse) {
            $activity = app(ActivityService::class)->handleCommunication(
                $this->selectedResponse->id,
                $communicationType,
                $status,
                "{$communicationType} activity updated"
            );

            $this->notifyStatus($activity, $communicationType);
            $this->dispatch('close-modal', id: 'communication-modal');
        }
    }

    protected function notifyStatus($activity, $communicationType)
    {
        $message = $activity ? "{$communicationType} activity updated successfully." : "Failed to update {$communicationType} activity.";
        $type = $activity ? 'success' : 'danger';

        Notification::make()
            ->$type()
            ->title($message)
            ->send();
    }

    public function filterStatus($status)
    {
        $this->statusFilter = $status;
        $this->loadResponses();
    }

    public function loadResponses($responseId = null)
    {
        $query = Response::where('seller_id', auth('seller')->id())
            ->with(['seller', 'activities' => fn ($q) => $q->orderBy('created_at', 'desc')]);

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $this->responses = $query->latest()->get();
        $response = $this->responses->first();
        $responseId = $responseId ?? $response?->id;


        if ($responseId) {
            $this->selectResponse($responseId);
        }

    }

    public function render()
    {
        return view('livewire.responses-list');
    }
}
