<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerRequestNotInterested extends Model
{
    use HasFactory;

    protected $table = "seller_request_not_interested";

    protected $fillable = ["seller_id", "request_id"];
}
