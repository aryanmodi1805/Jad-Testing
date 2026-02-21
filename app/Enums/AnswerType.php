<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum AnswerType: int implements HasLabel, HasColor,HasIcon
{
    case Date = 1;
    case Number = 2;
    case TextArea = 3;
    case Text = 4;


    public function getLabel(): ?string
    {
        return match ($this) {
            self::Date => __('localize.question.type.date'),
            self::Number =>__('localize.question.type.number'),
            self::Text => __('localize.question.type.text'),
            self::TextArea => __('localize.question.type.textarea'),
        };
    }

    public static function getType(string|AnswerType $type): AnswerType
    {
        if($type instanceof AnswerType){
            return $type;
        }

        foreach (self::cases() as $status) {
            if( $type === $status->name ){
                return $status;
            }
        }
        return AnswerType::Text;
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Date => "heroicon-m-calendar",
            self::Number => "heroicon-m-hashtag",
            self::Text => "heroicon-m-pencil",
            self::TextArea => "heroicon-m-document-text",
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Date => 'info',
            self::Number => 'secondary',
            self::Text => 'dark',
            self::TextArea => 'warning',
        };
    }
}
