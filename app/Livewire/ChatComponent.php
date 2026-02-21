<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Response;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ChatComponent extends Component
{
    public $responseId;
    public $messageText;
    public $disableChat;

    protected $rules = [
        'messageText' => 'required|string',
    ];

    public function mount($responseId,$disableChat=false)
    {
        $this->responseId = $responseId;
    }

    public function sendMessage()
    {
        $this->validate();

        $sender = Auth::user();
        $senderType = get_class($sender);

        Message::create([
            'response_id' => $this->responseId,
            'sender_id' => $sender->id,
            'sender_type' => $senderType,
            'message' => $this->messageText,
        ]);

        $this->messageText = '';

    }

    public function render()
    {
        $response = Response::with('messages.sender')->findOrFail($this->responseId);
        return view('livewire.chat-component', compact('response'));
    }
}
