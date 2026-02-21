<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;
//use JaOcero\FilaChat\Events\FilaChatMessageReadEvent;
//use JaOcero\FilaChat\Models\FilaChatConversation;
//use JaOcero\FilaChat\Models\FilaChatMessage;

class ListBox extends Field
{
    protected string $view = 'forms.components.list-box';

    public $selectedConversation;

    public function getSelectedConversation()
    {
        return $this->selectedConversation;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->hiddenLabel();

    }

    public function mount(?int $id = null): void
    {
//        if ($id) {
//            $this->selectedConversation = FilaChatConversation::findOrFail($id);
//
//            $message = FilaChatMessage::query()
//                ->where('filachat_conversation_id', $this->selectedConversation->id)
//                ->where('last_read_at', null)
//                ->where('receiverable_id', auth()->id())
//                ->where('receiverable_type', auth()->user()::class);
//
//            if ($message->exists()) {
//                $message->update(['last_read_at' => now()]);
//
//                broadcast(new FilaChatMessageReadEvent($this->selectedConversation->id));
//            }
//        }
    }
}
