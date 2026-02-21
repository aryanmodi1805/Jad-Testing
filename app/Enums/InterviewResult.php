<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InterviewResult: string implements HasLabel, HasColor
{
    case processing = 'processing';
    case accepted = 'accepted';
    case rejected = 'rejected';
    case favorites = 'favorites';


    public function getLabel(): ?string
    {


        return match ($this) {
            self::processing => __("string.applicants.interview_result.options.processing"),
            self::accepted => __("string.applicants.interview_result.options.accepted"),
            self::rejected => __("string.applicants.interview_result.options.rejected"),
            self::favorites => __("string.applicants.interview_result.options.favorites"),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::rejected => 'danger',
            self::favorites => 'warning',
            self::accepted => 'success',
            self::processing => 'primary',
        };
    }

}
