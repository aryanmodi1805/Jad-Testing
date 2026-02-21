<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerSocialMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id', 'platform', 'link','icon','active'
    ];
}
