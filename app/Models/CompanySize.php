<?php

namespace App\Models;

use App\Traits\HasFullNameTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class CompanySize extends Model
{

    use HasFactory;
    use \App\Traits\HasTranslations;
    use SoftDeletes;
    use HasFullNameTranslation;

    public $translatable = ['name'];

    protected $fillable = [
        'name', 'active', 'order', 'deleted_at',
    ];}
