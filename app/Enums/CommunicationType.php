<?php

namespace App\Enums;
use Illuminate\Support\Facades\View;

enum CommunicationType: string
{
    case Call = 'Call';
    case WhatsApp = 'WhatsApp';

    public function statuses(): array
    {
        return match ($this) {
            self::Call => ['No answer', 'Left voicemail', 'We talked'],
            self::WhatsApp => ['Number isn’t on WhatsApp', 'I couldn’t make it work', 'I sent a message'],
        };
    }

    public function colors(): array
    {
        return match ($this) {
            self::Call, self::WhatsApp => ['danger', 'info', 'success'],
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Call => 'phone',
            self::WhatsApp => 'chat',
        };
    }

    public function headerInfo(string $name = '', string $number = '', string $detail = 'Update your progress'): array
    {
        return match ($this) {
            self::Call => [
                'title' => "Call $name",
                'info' => View::make('components.call_info', ['number' => $number])->render(),
                'detail' => $detail
            ],
            self::WhatsApp => [
                'title' => "Did you message $name on WhatsApp?",
                'info' => View::make('components.whatsapp_info')->render(),
                'detail' => ''
            ],
        };
    }


}
