<?php

namespace  App\Concerns;

use App\Enums\MessageType;
use App\Enums\ResponseStatus;
use App\Events\MessageEvent;
use App\Models\Estimate;
use App\Models\EstimateBase;
use App\Models\Message;
use App\Models\Response;
use App\Models\Seller;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Log;

trait HasEstimate
{
    public static function getDefaultName(): ?string
    {
        return 'estimate';
    }


    public ?Response $response = null;

    public ?Estimate $estimate = null;

    public function response(Closure | Response | string $response) : static
    {
        $response = $this->evaluate($response);

        $this->response = $response instanceof Response ? $response : Response::find($response);

        $this->estimate = $this->response?->estimate()->latest()->firstOrNew();

        if($this->estimate != null){
            $this->fillForm($this->estimate->toArray());
        }

        $this->updateLabel();


        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon('heroicon-o-banknotes');

        $this->successNotificationTitle(__('string.estimate.success_note'));
        $this->failureNotificationTitle(__('string.estimate.send_failure')) ;

        $this->form([
            TextInput::make('amount')
                ->label(__('string.estimate.amount'))
                ->numeric()
                ->prefix(fn() => Filament::getTenant()?->currency?->symbol ?? getCurrentTenant()?->currency?->symbol ?? '*')
                ->required(),
            Select::make('estimate_base_id')
                ->label(__('string.estimate_basis.single'))
                ->options(EstimateBase::pluck('name', 'id'))
                ->required(),

            Textarea::make('details')
                ->label(__('string.estimate.details'))
                ->maxLength(255)
                ->required()
                ->rows(5),
        ]);

        if($this->response != null){
            $this->estimate = $this->response?->estimate()->latest()->firstOrNew();
        }
        $this->updateLabel();

        $this->disabledForm(fn()=>$this->response->refresh()->status  !==  ResponseStatus::Pending || $this->response->request->refresh()->status === \App\Enums\RequestStatus::Completed);
        $this->disabled(fn()=>$this->response->refresh()->status  !==  ResponseStatus::Pending || $this->response->request->refresh()->status === \App\Enums\RequestStatus::Completed);


        $this->action(function (array $data,$action): void {
            if($this->response != null){
                $this->response->refresh();
                if($this->response->status  !=  ResponseStatus::Pending || $this->response->request->refresh()->status === \App\Enums\RequestStatus::Completed)
                {
                    $action->failure();
                }

                $this->estimate = $this->estimate->updateOrCreate(["id" => $this->estimate->id], array_merge($data,[
                    'response_id' => $this->response->id,
                ]));

                $newMessage = Message::create([
                    'response_id' => $this->response->id,
                    'sender_id' => auth('seller')->id(),
                    'sender_type' => Seller::class,
                    'message' => 'Estimate updated',
                    'payload' => [
                        'type' => MessageType::Estimate,
                        'data' => $this->estimate->load('estimateBase')
                    ],
                    'type_type' => Estimate::class,

                ]);


                try {
                    broadcast(new MessageEvent(
                        $this->response->id,
                        $newMessage->id,
                        $newMessage->sender_id,
                    ));
                }catch (\Exception $exception){
                    Log::error($exception->getMessage());
                }


            }

        });


    }

    public function updateLabel(): void
    {
        if ($this->estimate == null || $this->estimate->id == null ) {
            $this->modalHeading(__('string.estimate.create'));
            $this->label(__('string.estimate.create'));
        } else {
            $this->modalHeading(__('string.estimate.update'));
            $this->label(__('string.estimate.update'));
        }
    }



}
