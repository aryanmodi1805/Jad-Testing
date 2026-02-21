<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Partner extends Model
{
    use HasFactory,SoftDeletes , HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = ['name', 'email', 'phone', 'address', 'image','show_on_homepage', 'active', 'deleted_at'];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
