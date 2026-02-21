<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum QuestionType: int implements HasLabel, HasColor,HasIcon
{
    case SELECT = 1;
    case Checkbox = 2;
    case Date = 3;
    case Number = 4;
    case TextArea = 5;
    case Text = 6;
    case Attachments = 7;
    case Location = 8;
    case PreciseDate = 9;
    case DateRange = 10;

    public function getLabel(): ?string
    {


        return match ($this) {
            self::SELECT => __('localize.question.type.select'),
            self::Checkbox => __('localize.question.type.checkbox'),
            self::Date => __('localize.question.type.date'),
            self::Number =>__('localize.question.type.number'),
            self::Text => __('localize.question.type.text'),
            self::TextArea => __('localize.question.type.textarea'),
            self::Attachments => __('localize.question.type.attachments'),
            self::Location => __('localize.question.type.location'),
            self::PreciseDate => __('localize.question.type.precise_date'),
            self::DateRange => __('localize.question.type.date_range'),

        };
    }

    public static function getType(string $type): QuestionType
    {

        foreach (self::cases() as $status) {
            if( $type === $status->name ){
                return $status;
            }
        }
        return QuestionType::Text;
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SELECT => "heroicon-m-cursor-arrow-ripple",
            self::Checkbox => "heroicon-m-check-circle",
            self::Date => "heroicon-m-calendar",
            self::Number => "heroicon-m-hashtag",
            self::Text => "heroicon-m-pencil",
            self::TextArea => "heroicon-m-document-text",
            self::Attachments => "heroicon-m-link",
            self::Location => "heroicon-m-map-pin",
            self::PreciseDate, self::DateRange => "heroicon-m-calendar-days",

        };
    }


    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SELECT => 'primary',
            self::Checkbox => 'success',
            self::Date => 'info',
            self::Number => 'secondary',
            self::Text => 'dark',
            self::TextArea => 'dark',
            self::Attachments => 'warning',
            self::Location => 'info',
            self::PreciseDate => 'info',
            self::DateRange => 'info',

        };
    }


}
