<?php

namespace App\Traits;
use Spatie\Translatable\HasTranslations as BaseHasTranslations;

trait HasTranslations
{
    use BaseHasTranslations;

    public function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
