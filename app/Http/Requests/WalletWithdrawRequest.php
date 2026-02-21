<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\BitcoinAddress;
use App\Rules\MinimumNum;
class WalletWithdrawRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        return [
            'amount'      => ['required', new MinimumNum, 'lte:'.$user->balance()->value],
            'destination' => ['required', new BitcoinAddress],
        ];
    }


}
