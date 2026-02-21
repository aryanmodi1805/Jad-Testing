<?php

namespace App\Rules;

use App\Models\Customer;
use App\Models\Seller;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public Customer|Seller $user;

    public string $guard = 'customer';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = [
            'email' => $this->user->email,
            'password' => $value,
        ];

        if (!auth($this->guard)->attempt($data)) {
            $fail(__('filament-panels::pages/auth/login.messages.failed'));
        }

    }

    public function __construct($user , $guard)
    {
        $this->user = $user;
        $this->guard = $guard;
    }
}
