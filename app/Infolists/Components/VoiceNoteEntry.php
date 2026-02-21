<?php

namespace App\Infolists\Components;

use Filament\Infolists\Components\Entry;
use Illuminate\Support\Facades\Storage;

class VoiceNoteEntry extends Entry
{
    protected string $view = 'infolists.components.voice-note-entry';

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function getVoiceNoteUrl(): ?string
    {
        $voiceNote = $this->getState();
        if ($voiceNote) {
            return Storage::disk('public')->url($voiceNote);
        }

        return null;
    }
}
