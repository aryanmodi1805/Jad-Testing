<?php
namespace App\Rules;

use App\Models\SellerLocation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OneNationwideLocation implements ValidationRule
{
    protected $sellerId;
    protected $currentRecordId;

    public function __construct($sellerId, $currentRecordId = null)
    {
        $this->sellerId = $sellerId;
        $this->currentRecordId = $currentRecordId instanceof Closure ? $currentRecordId() : $currentRecordId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value) {
            return;
        }

        $query = SellerLocation::where('seller_id', $this->sellerId)
            ->where('is_nationwide', true);

        if ($this->currentRecordId) {
            $query->where('id', '!=', $this->currentRecordId);
        }

        if ($query->exists()) {
            $fail('A nationwide location already exists for this seller.');
        }
    }
}
