<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\HasTranslations;
use App\Traits\HasFullNameTranslation;

class SellerProfileService extends Model
{

    use HasFactory,HasTranslations,HasFullNameTranslation;

    protected $fillable = [
        'seller_id', 'service_title', 'service_description',
    ];

    public $translatable = ['service_title', 'service_description'];
}
