<?php

namespace App\Livewire;

use App\Concerns\CanGetOriginalFileName;
use App\Concerns\CanValidateAudio;
use App\Concerns\CanValidateDocument;
use App\Concerns\CanValidateImage;
use App\Concerns\CanValidateVideo;
use App\Enums\ResponseStatus;
use App\Events\MessageEvent;
use App\Events\MessageReadEvent;
use App\Events\MessageReceiverIsAwayEvent;
use App\Filament\Actions\FormEstimateAction;
use App\Filament\Actions\PageEstimateAction;
use App\Filament\Customer\Resources\RequestResource\Pages\ViewRequest;
use App\Models\BlockReason;
use App\Models\BlockReport;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Response;
use App\Models\Seller;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[On('chat-refresh')]
class ItemBox extends Component implements HasForms, HasActions
{
    use CanGetOriginalFileName;
    use CanValidateAudio;
    use CanValidateDocument;
    use CanValidateImage;
    use CanValidateVideo;
    use InteractsWithForms;
    use InteractsWithActions;
    use WithPagination;

    public ?Response $selectedConversation;

    public ?Model $currentUser;
    public ?Model $otherUser;

    public bool $hasEstimate = false;
    public bool $isAdmin = false;

    public $currentPage = 1;

    public Collection $conversationMessages;

    public ?array $data = [];

    public bool $showUpload = false;

    public bool $isDisabled = false;

    public function mount(): void
    {
        $this->form->fill();
        $this->currentUser = Filament::auth()->user();
        $this->otherUser = $this->selectedConversation->seller_id == $this->currentUser->id ? $this->selectedConversation->customer : $this->selectedConversation->seller;

        $this->isAdmin = !instanceOfAny(Filament::auth()->user(),[Customer::class, Seller::class]);

        $this->isDisabled = $this->isAdmin
                            || $this->selectedConversation->status != ResponseStatus::Pending
                            || $this->selectedConversation->isBlocked($this->currentUser, $this->otherUser);

        if(!$this->isAdmin) {
            $messagesNotRead = false;

            $this->selectedConversation->messages()->where('sender_id', '!=', Filament::auth()->id())->whereNull('read_at')->each(function ($message) use (&$messagesNotRead) {
                $message->read_at = now();
                $message->save();
                $messagesNotRead = true;
            });

            if ($messagesNotRead) {
                broadcast(new MessageReadEvent($this->selectedConversation->id));
            }
        }

        if ($this->selectedConversation) {
            $this->hasEstimate = $this->selectedConversation->estimate()->exists();
            $this->conversationMessages = collect();
            $this->loadMoreMessages();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(1)->schema([
                Forms\Components\FileUpload::make('attachments')
                    ->hiddenLabel()
                    ->multiple()
                    ->directory('chat_attachments')
                    ->storeFileNamesIn('original_attachment_file_names')
                    ->panelLayout('grid')
                    ->extraAttributes([
                        'class' => 'mb-6 chat-filepond',
                    ])
                    ->visible(fn () => $this->showUpload),

                Forms\Components\Textarea::make('message')
                    ->hiddenLabel()
                    ->placeholder(__('string.chat.write_message'))
                    ->required(function (Get $get) {
                        if (count($get('attachments')) > 0) {
                            return false;
                        }
                        return true;
                    })
                    ->extraAlpineAttributes([
                        'x-on:keydown' => 'function(o) {
                                  o = o || event;
                                  if (o.shiftKey && o.keyCode == 13) {
                                    /* shift + enter pressed */
                                  }else if (o.keyCode == 13) {
                                    o.preventDefault();
                                    $wire.sendMessage();

                                  }
                            }',
                        'x-on:sendMessage' => 'console.log("send message")',
                        'wire:target' => 'sendMessage',
                        'wire:loading.attr' => 'disabled',
                        'wire:loading.class' => 'cursor-not-allowed pointer-events-none animate-bounce',

                    ])
                    ->rows(3)
                    ->autosize()
                    ->grow(true),
                ])->disabled(fn() => $this->isDisabled),
                Forms\Components\Actions::make([
                    FormEstimateAction::make('estimate-action')
                        ->response($this->selectedConversation)
                        ->visible(fn() => Filament::getAuthGuard() == 'seller')
                        ,

                    Forms\Components\Actions\Action::make('blockAndReport')
                        ->label(__('string.chat.block_and_report'))
                        ->requiresConfirmation()
                        ->color('danger')
                        ->modalDescription(__('string.chat.block_and_report_description'))
                        ->form([
                            Forms\Components\Select::make('block_reason_id')
                                ->required()
                                ->options(BlockReason::pluck('name', 'id'))
                                ->label(__('string.chat.block_and_report_reason')),
                            Forms\Components\Textarea::make('details')
                                ->label(__('string.chat.block_and_report_details')),
                        ])->action(function ($data){
                            BlockReport::create([
                                'reference_id' => $this->selectedConversation->id,
                                'reference_type' => $this->selectedConversation::class,
                                'blocked_id' => $this->otherUser->id,
                                'blocked_type' => $this->otherUser::class,
                                'blocker_id' => $this->currentUser->id,
                                'blocker_type' => $this->currentUser::class,
                                'block_reason_id' => $data['block_reason_id'],
                                'details' => $data['details'],
                            ]);

                            if($this->currentUser instanceof Customer){
                                Response::query()
                                    ->join('requests', 'responses.request_id', '=', 'requests.id')
                                    ->where('requests.customer_id', $this->currentUser->id)
                                    ->where('responses.status' , ResponseStatus::Pending)

                                    ->where('seller_id', '=', $this->otherUser->id)->update([
                                    'responses.status' => ResponseStatus::Rejected
                                ]);
//                                $this->selectedConversation->update([
//                                    'status' => ResponseStatus::Rejected
//                                ]);
                            }else if($this->currentUser instanceof Seller){
                                Response::query()
                                    ->join('requests', 'responses.request_id', '=', 'requests.id')
                                    ->where('requests.customer_id', $this->otherUser->id)
                                    ->where('responses.status' , ResponseStatus::Pending)
                                    ->where('seller_id', '=', $this->currentUser->id)->update([
                                        'responses.status' => ResponseStatus::Rejected
                                    ]);
//                                $this->selectedConversation->update([
//                                    'status' => ResponseStatus::Cancelled
//                                ]);
                            }
                            redirect( ViewRequest::getUrl([
                                'record' => $this->selectedConversation->request,
                                'tenant' => getCurrentTenant()
                            ], panel: 'customer'));

                        })->disabled(fn() =>  $this->isDisabled)
                ])->extraAttributes([
                    'class' => 'hidden',
                ])
            ])
            ->columns('full')
            ->extraAttributes([
                'class' => 'p-1 !gap-0',
            ])
            ->statePath('data');
    }
    public function groupAction(): ActionGroup{
        return ActionGroup::make([
            Action::make('show_hide_upload')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('gray')
                ->label($this->showUpload ? __('string.chat.hide_upload') : __('string.chat.upload'))
                ->disabled(fn() =>  $this->isDisabled)
                ->action('showHideUpload'),
            Action::make('page-estimate-action')
                ->color('primary')
                ->icon('heroicon-o-banknotes')
                ->label($this->hasEstimate? __('string.estimate.update') : __('string.estimate.create'))
                ->visible(fn() => Filament::getAuthGuard() == 'seller' &&
                    $this->selectedConversation->status !== \App\Enums\ResponseStatus::Hired &&
                    $this->selectedConversation->request->status !== \App\Enums\RequestStatus::Completed
                )
                ->disabled(fn() =>  $this->isDisabled)
                ->action('openEstimate'),

            Action::make('block_and_report')
                ->icon('icon-block')
                ->color('danger')
                ->label( __('string.chat.block_and_report'))
                ->disabled(fn() =>  $this->isDisabled)
                ->action('blockAndReport'),

        ])->icon('heroicon-o-plus')
            ->color('gray')
            ->size('lg')
            ->hiddenLabel()
            ->button()
            ->dropdownPlacement('top-end');

    }

    public function showHideUpload(): void
    {
        $this->showUpload = !$this->showUpload;
    }

    public function openEstimate(): void
    {
        $this->mountFormComponentAction('data.estimate-actionAction', 'estimate-action');
    }
    public function blockAndReport(): void
    {
        $this->mountFormComponentAction('data.blockAndReportAction', 'blockAndReport');
    }


    public function sendAction(): Action
    {
        return Action::make('send')
            ->icon('heroicon-m-paper-airplane')
            ->hiddenLabel()
            ->livewireTarget('sendMessage')
            ->size('lg')
            ->color('primary')
            ->extraAttributes(fn()=> [
                'style' => app()->getLocale() == "ar" ? 'rotate:180deg;' : ''
            ])
            ->tooltip(__('string.chat.send'))
            ->disabled(fn() =>  $this->isDisabled)
            ->action('sendMessage');
    }
    public function sendMessage(): void
    {
        $this->js(<<<JS
            window.dispatchEvent(new CustomEvent('send-message'));
        JS);
        $data = $this->form->getState();

        try {
            DB::transaction(function () use ( $data) {
                $newMessage = Message::query()->create([
                    'response_id' => $this->selectedConversation->id,
                    'message' => $data['message'] ?? null,
                    'sender_id' => $this->currentUser->id,
                    'sender_type' =>  $this->currentUser::class,
                    'attachments' => $data['attachments'] ?? [],
                    'original_attachment_file_names' => $data['original_attachment_file_names'] ?? [],
                ]);

                $this->conversationMessages->prepend($newMessage);

                $this->showUpload = false;

                $this->form->fill();

                $this->selectedConversation->updated_at = now();

                $this->selectedConversation->save();

                try {
                    broadcast(new MessageEvent(
                        $this->selectedConversation->id,
                        $newMessage->id,
                        $newMessage->sender_id,
                    ));
                }catch (\Exception $exception){
                    Log::error($exception->getMessage());
                }
            });
        } catch (\Exception $exception) {
            Notification::make()
                ->title(__('requests.unknown_error'))
                ->body($exception->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    #[On('echo:chat,.App\\Events\\MessageEvent')]
    public function broadcastNewMessage($data): void
    {
        if ($data['type'] === MessageEvent::class) {

            /**
             * This will only be executed if the conversation
             * is the selected conversation
             */
            if ($data['conversationId'] && $data['conversationId'] === $this->selectedConversation?->id) {

                $message = Message::find($data['messageId']);

                if ($message) {
                    $this->conversationMessages->prepend($message)->values();

                }

                if ($message && $message->sender_id != Filament::auth()->id() && !$this->isAdmin) {
                    $message->read_at = now();
                    $message->save();

                    broadcast(new MessageReadEvent($this->selectedConversation->id));
                }
            }
        }
    }

    public function loadMoreMessages()
    {
        $messages = $this->paginator->getCollection();

        $this->conversationMessages->push(...$messages);

        $this->currentPage = $this->currentPage + 1;

        $this->dispatch('chat-box-preserve-scroll-position');
    }

    #[Computed()]
    public function paginator()
    {
        return $this->selectedConversation->messages()->latest()->paginate(10, ['*'], 'page', $this->currentPage);
    }

    public function downloadFile(string $path, string $originalFileName)
    {
        // Check if the file exists
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path, $originalFileName);
        }

        abort(404, 'File not found');
    }
    public function render()
    {
        return view('livewire.item-box');
    }

}
