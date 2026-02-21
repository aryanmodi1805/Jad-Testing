<?php

namespace App\Extensions;

use Filament\Facades\Filament;
use Illuminate\Notifications\Messages\MailMessage;


class JadMailMessage extends MailMessage {
    public string $direction = 'rtl';

    public function setDirection($direction): static
    {

        $this->direction = $direction;

        return $this;
    }

    public function getDirection() {
        return $this->direction;
    }

    public function toArray()
    {
        return [
            'level' => $this->level,
            'subject' => $this->subject,
            'greeting' => $this->greeting,
            'salutation' => $this->salutation,
            'introLines' => $this->introLines,
            'outroLines' => $this->outroLines,
            'actionText' => $this->actionText,
            'actionUrl' => $this->actionUrl,
            'direction' => $this->getDirection(),
            'displayableActionUrl' => str_replace(['mailto:', 'tel:'], '', $this->actionUrl ?? ''),
        ];
    }
}
