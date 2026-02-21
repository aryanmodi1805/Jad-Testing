<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $fillable = [
        'subscription_id', 'main_category_id', 'sub_category_id', 'service_id', 'quantity', 'type',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'main_category_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

      public function getName($type)
      {
          $name ="-";
          $this->type  == $type ;
         $name = $this->main_category_id ?
             $this->category?->name :
             ( $this->sub_category_id ? $this->subCategory?->name :
                 ($this->service_id ? $this->service?->name : ''));

          return $name;

      }
}
