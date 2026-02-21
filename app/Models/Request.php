<?php

namespace App\Models;

use App\Enums\QuestionType;
use App\Enums\RequestStatus;
use App\Enums\ResponseStatus;
use App\Interfaces\CanPayItem;
use App\Observers\RequestObserver;
use App\Traits\HasCountryScope;
use App\Traits\Rateable;
use App\Traits\Wallet\Purchasable;
use Carbon\Carbon;
use DB;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(RequestObserver::class)]
class Request extends Model implements CanPayItem
{
    use HasFactory, HasUuids, Purchasable, HasCountryScope, Rateable;

    protected $fillable = ['customer_id', 'service_id', 'status', 'location_name', 'latitude', 'longitude', 'location_type', 'country_id'];

    protected $casts = [
        'status' => RequestStatus::class,
    ];

    /**
     * Get the lat and lng attribute/field names used on this table
     *
     * Used by the Filament Google Maps package.
     *
     * @return string[]
     */
    public static function getLatLngAttributes(): array
    {
        return [
            'lat' => 'latitude',
            'lng' => 'longitude',
        ];
    }

    /**
     * Get the name of the computed location attribute
     *
     * Used by the Filament Google Maps package.
     *
     * @return string
     */
    public static function getComputedLocation(): string
    {
        return 'location';
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id')->withoutGlobalScopes();
    }

    public function services(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function formattedAnswers(): array
    {
        $answers = $this->customerAnswers;
        $formattedAnswers = [];
        foreach ($answers as $answer) {
            /* @var $answer CustomerAnswer */

            $data = [
                'question_label' => $answer->question_label,
                'question_sort' => $answer->question_sort,
                'question_type' => $answer->question_type,
            ];
            switch ($answer->question_type) {
                case QuestionType::SELECT:
                    $data['answer_label'] = handleCustomAnswer($answer);
                    break;
                case QuestionType::Checkbox:
                    if (isset($formattedAnswers[$answer->question_id]["answer_label"],)) {
                        if (is_array($formattedAnswers[$answer->question_id]["answer_label"])) {
                            $data['answer_label'] = array_merge($formattedAnswers[$answer->question_id]["answer_label"], [handleCustomAnswer($answer)]);
                        } else {
                            $data['answer_label'] = array_merge([$formattedAnswers[$answer->question_id]["answer_label"]], [handleCustomAnswer($answer)]);
                        }
                    } else {
                        $data['answer_label'] = handleCustomAnswer($answer);
                    }
                    break;
                case QuestionType::Date:
                case QuestionType::Number:
                case QuestionType::TextArea:
                case QuestionType::Text:
                case QuestionType::DateRange:
                    $data['text_answer'] = $answer->text_answer;
                    break;
                case QuestionType::Attachments:
                    $data['attachments'] = $answer->attachment;
                    $data['voice_note'] = $answer->voice_note;
                    break;
                case QuestionType::Location:
                    $data['location_name'] = $answer->text_answer;
                    $data['location'] = $answer->location;
                    break;
                case QuestionType::PreciseDate:
                    $data['text_answer'] = getPreciseDateString($answer);
                    break;

            }

            $formattedAnswers[$answer->question_id] = $data;
        }
        return $formattedAnswers;
    }

    public function sellers(): BelongsToMany
    {
        return $this->belongsToMany(Seller::class, 'responses')
            ->withPivot('status', 'notes')
            ->withTimestamps();
    }

    public function cancelInvitation(): void
    {
        $this->responses()->where('status', ResponseStatus::Invited)->where('seller_id', auth('seller')->id())->update(['status' => ResponseStatus::Cancelled]);
    }

    public function sellerNotInterested(): void
    {
        $this->cancelInvitation();
        SellerRequestNotInterested::create([
            'seller_id' => auth('seller')->id(),
            'request_id' => $this->id
        ]);

    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class, 'request_id');
    }

    public function seller_responses(): HasMany
    {
        return $this->hasMany(Response::class, 'request_id')
            ->where('status', '!=', ResponseStatus::Invited->value);
    }

    public function invites(): HasMany
    {
        return $this->hasMany(Response::class, 'request_id')
            ->where('status', '=', ResponseStatus::Invited->value);
    }



    public function customerAnswers(): HasMany
    {
        return $this->hasMany(CustomerAnswer::class, 'request_id')->orderBy('question_sort');
    }

    public function getFinalPrice(): float
    {
        $isSubscribed = auth('seller')->check() ? auth('seller')->user()->isSubscribedToService($this->service_id) : auth()->user()->isSubscribedToService($this->service_id);
        if($isSubscribed){
            return 0;
        }

        return $this->request_total_cost  ?? $this->customerAnswers()->sum('val') ?? 0;

    }



    public function getWalletMeta(): array
    {
        return [
            'data' => __('wallet.pay') . __('requests.Request') . '# ' .
                $this->service?->name .' # '. $this->customer?->name,

        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function getPaymentTitle(): string
    {
        return 'Request#  [' . $this->customer?->name . ']';
    }


    public function scopeRecentRequests(Builder $query, $serviceId, $minutes = 10): Builder
    {
        return $query->where('service_id', $serviceId)
            ->where('created_at', '>=', Carbon::now()->subMinutes($minutes));
    }

    /**
     * Scope a query to add geospatial filtering.
     *
     * @param Builder $query
     * @param float $longitude
     * @param float $latitude
     * @param int $rangeInMeters
     * @return Builder
     */
    public function scopeWithProximity(Builder $query, float $longitude, float $latitude, int $rangeInMeters): Builder
    {
        return $query->whereRaw("ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?", [
            $longitude, $latitude, $rangeInMeters
        ]);
    }

    public function getLocationAttribute(): array
    {
        return [
            "lat" => (float)$this->latitude,
            "lng" => (float)$this->longitude,
        ];
    }

    /**
     * Takes a Google style Point array of 'lat' and 'lng' values and assigns them to the
     * 'latitude' and 'longitude' attributes on this model.
     *
     * Used by the Filament Google Maps package.
     *
     * Requires the 'location' attribute be included in this model's $fillable array.
     *
     * @param ?array $location
     * @return void
     */
    public function setLocationAttribute(?array $location): void
    {
        if (is_array($location)) {
            $this->attributes['latitude'] = $location['lat'];
            $this->attributes['longitude'] = $location['lng'];
            unset($this->attributes['location']);
        }
    }

    public function notInterested() : HasMany
    {
        return $this->hasMany(SellerRequestNotInterested::class );
    }

    public function scopeNotBlockedSeller(Builder $query, $seller): Builder
    {
        $customers  = BlockReport::selectRaw("
                CASE
                    WHEN blocked_type = ? THEN blocker_id
                    WHEN blocker_type = ? THEN blocked_id
                END as customer_id", [Seller::class, Seller::class])
            ->where(fn($query) => $query
                ->where('blocked_id', $seller->id)
                ->where('blocked_type', Seller::class))
            ->orWhere(fn($query) => $query
                ->where('blocker_id', $seller->id)
                ->where('blocker_type', Seller::class))
            ->toRawSql();

        return $query->whereRaw("customer_id not in ($customers)");
    }


    public function scopeCanBeServedBySeller(Builder $query, $seller): Builder
    {
        return $query
            ->notBlockedSeller($seller)
            ->doesntHave('responses', 'and', function ($query) use ($seller) {
                $query->where('seller_id', $seller->id)->where('status', '!=', ResponseStatus::Invited->value);
            })
            ->whereDoesntHave('notInterested', function ($query) use ($seller) {
                $query->where('seller_id', $seller->id);
            })
            ->joinSub(
                Request::query()
                    ->selectRaw("subquery.request_id, (subquery.distance) as distance , subquery.nearest_location_name")->joinSub(
                        DB::query()
                            ->selectRaw('* , ROW_NUMBER() OVER (PARTITION BY request_distance.request_id ORDER BY distance) as distance_order')
                            ->fromSub(Request::query()
                                ->join('seller_services', 'requests.service_id', '=', 'seller_services.service_id')
                                ->selectRaw("requests.id as request_id, request_distance(requests.latitude, requests.longitude, seller_locations.latitude, seller_locations.longitude) as distance, seller_locations.name as nearest_location_name")
                                ->leftJoin('seller_service_locations', 'seller_services.id', '=', 'seller_service_locations.seller_service_id')
                                ->leftJoin('seller_locations', 'seller_service_locations.seller_location_id', '=', 'seller_locations.id')
                                ->whereColumn('seller_services.service_id', '=', 'requests.service_id')
                                ->where('seller_services.seller_id', $seller->id)
                                ->whereIn('requests.status', [RequestStatus::Open, RequestStatus::Booking])
                                ->where('requests.country_id', $seller->country_id)
                                ->whereNotNull('seller_locations.latitude')
                                ->whereNotNull('seller_locations.longitude')
                                ->whereRaw("(seller_service_locations.is_nationwide = 1 OR request_distance(requests.latitude, requests.longitude, seller_locations.latitude, seller_locations.longitude) <= seller_service_locations.location_range)")
                                ->orderBy('distance', 'asc')
                                , 'request_distance')
                        , 'subquery', 'requests.id', '=', 'subquery.request_id'
                    )
                    ->where('subquery.distance_order', 1)
                    ->orderBy('distance', 'asc'),
                'location_distance', 'location_distance.request_id', '=', 'requests.id');
    }

}
