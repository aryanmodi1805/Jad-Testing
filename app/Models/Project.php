<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
use App\Traits\HasTranslations;
use App\Traits\HasFullNameTranslation;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Project extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia,HasTranslations,HasFullNameTranslation;



    protected $fillable = [
        'seller_id',
        'title',
        'description',
        'image'
    ];

    public $translatable = ['title', 'description'];

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->width(336)
            ->height(252)
            ->nonQueued();
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
