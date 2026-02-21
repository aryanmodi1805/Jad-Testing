<?php
namespace App\Casts;

use App\Enums\AnswerType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class AnswerTypeCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return $value != null ? AnswerType::from($value) : null;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value instanceof AnswerType || is_null($value)) {
            return $value?->value;
        }
        return null;
    }
}
