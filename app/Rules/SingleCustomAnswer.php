<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SingleCustomAnswer implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isCustomCount = collect($value)->where('is_custom', true)->count();
        if ($isCustomCount > 1) {
            $fail(__('validation.one_custom'));
        }
    }
}
