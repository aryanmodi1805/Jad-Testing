<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class EstimateBase extends Model
{
    use HasFactory, SoftDeletes , HasTranslations;

    public $translatable = ['name'];


    protected $fillable = ['name', 'active'];
}
