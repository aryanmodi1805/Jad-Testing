<?php

namespace App\Models;

use App\Models\Scopes\CategoriesActiveScope;
use App\Traits\HasFullNameTranslation;
use App\Traits\HasTranslations;
use DB;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use SolutionForest\FilamentTree\Concern\ModelTree;

#[ScopedBy(CategoriesActiveScope::class)]
class Category extends Model
{
    use HasFactory;
    use HasUuids;
    use ModelTree;
    use HasTranslations;
    use SoftDeletes;
    use HasFullNameTranslation;

    public $translatable = ['name'];
    protected $appends = ['icon_front'];

    protected $fillable = [
        'name', 'parent_id', 'active', 'order', 'deleted_at', 'icon', 'image',
    ];
    public function scopeActive()
    {
        return $this->where('active', true);
    }

    public static function defaultParentKey()
    {
        return null;
    }
    public function determineTitleColumnName(): string
    {
        return 'full_name';
    }

    public function getIconFrontAttribute()
    {
        return str_replace("tabler-", "ti ti-", $this->icon);
    }

    public function root(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id')->where('parent_id', null);
    }

    function services(): HasMany
    {
        return $this->hasMany(Service::class,  'category_id');
    }

    function categoryServices(): HasManyThrough
    {
        return $this->hasManyThrough(Service::class, Category::class, 'parent_id', 'category_id');
    }

    function requests(): HasManyThrough
    {
        return $this->hasManyThrough(Request::class, Service::class);

    }
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }



//    public function scopePopularCategory(Builder $query, int $limit = 10): Builder
//    {
//        return $query->with('services' , fn($q)=>$q->with('requests',fn($q)=>$q->))
//            ->orderBy('requests_count', 'desc')
//            ->take($limit);
//    }

    public function scopeWithRequestCount(Builder $query): Builder
    {
        return $query->withCount(['services as request_count' => function ($q) {
            $q->select(DB::raw('COUNT(requests.id)'))
                ->join('requests', 'requests.service_id', '=', 'services.id');
        }]);
    }

    public function getPopularCategoriesWithServices(int $limit = 9)
    {
        // جلب الفئات الفرعية مع الفئة الأم
        $childrenWithRequestCount = $this->childrenWithRequestCount()->get();

        // حساب عدد الطلبات للفئة الأم
        $this->loadCount(['services as request_count' => function ($query) {
            $query->join('requests', 'requests.service_id', '=', 'services.id');
        }]);

        // دمج الفئة الأم مع الفئات الفرعية
        $allCategories = $childrenWithRequestCount->prepend($this)
            ->sortByDesc('request_count')
            ->take($limit);

        // جلب جميع الخدمات لكل فئة شعبية
        $allCategories->load('services');

        return $allCategories->flatMap->services;
    }

    public function childrenWithRequestCount()
    {
        return $this->children()
            ->withCount(['services as request_count' => function ($query) {
                $query->join('requests', 'requests.service_id', '=', 'services.id');
            }]);
    }

    public function subscriptionItems(): hasMany
    {
        return $this->hasMany(SubscriptionItem::class, 'sub_category_id');

    }

    public function mainSubscriptionItems(): hasMany
    {
        return $this->hasMany(SubscriptionItem::class, 'main_category_id');

    }

    public function scopeUnsubscribed(Builder $query,$type = "credit"): Builder
    {
        $ids = SubscriptionItem::whereHas('subscription', function ($query) {
            $query->where('seller_id', auth('seller')->user()->id)->active();
        })
            ->select('main_category_id as id')
            ->whereNotNull('main_category_id')
            ->unionAll(
                SubscriptionItem::whereHas('subscription', function ($query) {

                    $query->where('seller_id', auth('seller')->user()->id)->active();
                })
                    ->select('sub_category_id as id')->whereNotNull('sub_category_id')
            )
            ->whereNotNull('id')
            ->where('type', $type)
            ->distinct();

        return $query->whereNotIn('id', $ids);
    }

}

