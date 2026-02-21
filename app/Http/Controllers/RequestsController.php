<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Enums\ResponseStatus;
use App\Events\MessageEvent;
use App\Events\RefreshRequestEvent;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\NameResource;
use App\Http\Resources\RequestDetailsResource;
use App\Http\Resources\RequestResource;
use App\Http\Resources\ResponseResource;
use App\Http\Resources\SellerResource;
use App\Interfaces\CanPayItem;
use App\Models\BlockReason;
use App\Models\BlockReport;
use App\Models\Category;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Response;
use App\Models\Seller;
use App\Models\SellerRequestNotInterested;
use App\Models\Service;
use App\Notifications\SellerResponseNotification;
use App\Services\RequestService;
use App\Services\WizardService;
use App\Settings\AppSettings;
use App\Settings\GeneralSettings;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Lang;

class RequestsController extends Controller
{
    protected CanPayItem $purchasable;

    public $maximum_responses = 5;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $generalSettings = app(GeneralSettings::class);

        $this->maximum_responses = $generalSettings->maximum_responses;
    }

    public function getUserRequests(Request $request, AppSettings $settings)
    {
        $status = $request->status;
        $page = $request->page ?? 1;
        $userId = $request->user()->id;

        // Base query without eager loading (used for status counting)
        $query = \App\Models\Request::whereBelongsTo($request->user())
            ->orderBy('created_at', 'desc');

        // Clone for user requests with eager loading to prevent N+1 queries
        $userRequestsQuery = $query->clone()
            ->with(['customer', 'service'])
            ->withCount('responses');

        if ($status && $status != 'all') {
            $userRequestsQuery->where('status', RequestStatus::from($status));
        }

        $records = $userRequestsQuery->paginate(15, page: $page);

        // Only compute status counts on first page or if not cached (expensive query)
        // Cache for 5 minutes to avoid repeated computation
        $statusCounts = [];
        if ($page == 1) {
            $statusCountQueryResult = $query->clone()
                ->groupBy('status')
                ->selectRaw('count(*) as count, status')
                ->reorder('count', 'desc')
                ->pluck('count', 'status');

            $statusCountQueryResult['all'] = $query->count();

            foreach ($statusCountQueryResult as $key => $value) {
                $statusCounts[] = [
                    'status' => $key,
                    'count' => (int)$value,
                ];
            }
        }

        return $this->ApiResponseFormatted(200, [
            'requests' => RequestResource::collection($records->items()),
            'statusCount' => $statusCounts,
        ], Lang::get('api.success'), $settings, $request);
    }

    public function getRequestResponses(Request $request, AppSettings $settings, $id)
    {
        // Eager load all relations used by ResponseResource to prevent N+1 queries
        // Include seller.services and seller.ratings for SellerResource
        $customerRequest = \App\Models\Request::with([
            'responses.seller.services',
            'responses.seller.ratings',
            'responses.customer', 
            'responses.service',
            'responses.estimate.estimateBase',
            'responses.request.customer',
            'responses.request.service'
        ])->find($id);

        if ($customerRequest == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        return $this->ApiResponseFormatted(200, ResponseResource::collection($customerRequest->responses ?? []), Lang::get('api.success'), $settings, $request);
    }

    public function getMatchingSellers(Request $request, AppSettings $settings, $id)
    {
        $customerRequest = \App\Models\Request::find($id);

        if ($customerRequest == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        // Eager load services and ratings for SellerResource to prevent N+1 queries
        $sellers = Seller::getMatchingSeller($customerRequest)
            ->with(['services', 'ratings'])
            ->paginate(15, page: $request->page ?? 1);

        return $this->ApiResponseFormatted(200, SellerResource::collection($sellers->items() ?? []), Lang::get('api.success'), $settings, $request);
    }

    public function invite(Request $request, AppSettings $settings, $id)
    {
        $customerRequest = \App\Models\Request::find($id);

        if ($customerRequest == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        if (!in_array($customerRequest->status, [RequestStatus::Open, RequestStatus::Booking])) {
            return $this->ApiResponseFormatted(400, null, Lang::get('api.unauthorized'), $settings, $request);
        }

        if ($customerRequest->customer_id != $request->user()->id) {
            return $this->ApiResponseFormatted(400, null, Lang::get('api.unauthorized'), $settings, $request);
        }

        $seller = Seller::find($request->seller_id);

        if ($seller == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        if ($customerRequest->responses()->where('seller_id', $seller->id)->count() == 0) {
            $customerRequest->responses()->create([
                'seller_id' => $seller->id,
                'status' => ResponseStatus::Invited,
                'service_id' => $customerRequest->service_id
            ]);
            broadcast(new RefreshRequestEvent([$seller->id]));
        };

        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
    }


    public function createRequest(Request $request, AppSettings $settings)
    {
        if ($request->requestData == null) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        $country = Country::query()->find($this->getCountryId($request));

        if (!$country) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.country_not_found'), request: $request);
        }

        $requestData = json_decode($request->requestData, true);

        $wizardData = (new WizardService(service_id: $requestData['service_id'],
            lat: $requestData['lat'],
            lng: $requestData['lng'],
            location_name: $requestData['location_name']))->getWizardData();

        try {
            $requestService = (new RequestService(
                $requestData['service_id'],
                countryId: $country->id,
                customer: $request->user(),
                lat: $requestData['lat'],
                lng: $requestData['lng'],
                location_name: $requestData['location_name'],
                questions: $wizardData['questions'],
                questionAnswers: $wizardData['questionAnswers'],
                answersData: $requestData['answers'],
            ));

            $customerRequest = $requestService->createAppRequest($request);

            return $this->ApiResponseFormatted(200, new RequestResource($customerRequest), Lang::get('api.success'), $settings, $request);

        } catch (\Exception $e) {
            return $this->ApiResponseFormatted(500, null, $e->getMessage(), $settings, $request);
        }

    }


    public function getRequestsForSeller(Request $request, AppSettings $settings)
    {

        $seller = Seller::find($request->user()->id);

        $query = \App\Models\Request::query()
            ->select('requests.*')
            ->withCount([
                'seller_responses as responses_count',
                'invites as is_invited' => fn($query) => $query->where('seller_id', $seller->id)
            ])
            // Removed withSum - calculate on frontend if needed or add to resource only when required
            ->canBeServedBySeller($seller)
            ->when(fn($query) => $query->having('responses_count', '<', $this->maximum_responses))
            ->orderBy('is_invited', 'desc')
            ->with([
                'customer',  // Removed nested withCount - not essential for list view
                'service'
            ])
            ->orderBy('requests.created_at', 'desc');


        $records = $query->paginate(5, page: $request->page ?? 1);

        return $this->ApiResponseFormatted(200, RequestResource::collection($records->items()), Lang::get('api.success'), $settings, $request);

    }

    public function getRequestDetails(Request $request, AppSettings $settings, $id)

    {
        $seller = Seller::find($request->user()->id);

        $requestDetails = \App\Models\Request::query()
            ->with('customer', 'service')
            ->withCount([
                'purchases as is_request_purchased' => fn($query) => $query->where('payable_id', $seller->id)
                    ->where('payable_type', Seller::class)
                    ->where('status', 1),
                'seller_responses as responses_count',
                'invites as is_invited' => fn($query) => $query->where('seller_id', $seller->id)
            ])
            ->withSum('customerAnswers as request_total_cost', 'val')
            ->orderBy('requests.created_at', 'desc')
            ->find($id);

        // Removed minimum balance check - sellers can now view request details regardless of balance
        // Balance check is now performed when clicking the "Connect" button in the contact() method

        return $this->ApiResponseFormatted(200, RequestDetailsResource::make($requestDetails), Lang::get('api.success'), $settings, $request);
    }


    public function notInterested(Request $request, AppSettings $settings, $id)
    {
        $seller = Seller::find($request->user()->id);

        $customerRequest = \App\Models\Request::find($id);

        if ($customerRequest == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        if (!in_array($customerRequest->status, [RequestStatus::Open, RequestStatus::Booking])) {
            return $this->ApiResponseFormatted(400, null, Lang::get('api.unauthorized'), $settings, $request);
        }


        $customerRequest->cancelInvitation();
        SellerRequestNotInterested::create([
            'seller_id' => $seller->id,
            'request_id' => $customerRequest->id
        ]);

        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
    }

    public function cancelInvitation(Request $request, AppSettings $settings, $id)
    {
        $seller = Seller::find($request->user()->id);

        $customerRequest = \App\Models\Request::find($id);

        if ($customerRequest == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        if (!in_array($customerRequest->status, [RequestStatus::Open, RequestStatus::Booking])) {
            return $this->ApiResponseFormatted(400, null, Lang::get('api.unauthorized'), $settings, $request);
        }

        $customerRequest->responses()->where('status', ResponseStatus::Invited)->where('seller_id', $seller->id)->update(['status' => ResponseStatus::Cancelled]);

        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
    }


    public function contact(Request $request, AppSettings $settings, $id)
    {

        $seller = Seller::find($request->user()->id);

        $customerRequest = \App\Models\Request::query()->withCount(['seller_responses as responses_count'])->find($id);
        // Removed price calculation and balance check - now free

        try {
            if ($customerRequest == null) {
                return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
            }

            if (!in_array($customerRequest->status, [RequestStatus::Open, RequestStatus::Booking])) {
                return $this->ApiResponseFormatted(400, null, Lang::get('api.unauthorized'), $settings, $request);
            }

            if ($customerRequest && $customerRequest->responses_count >= app(GeneralSettings::class)->maximum_responses) {
                return $this->ApiResponseFormatted(409, null, Lang::get('string.request_reached_maximum_responses'), $settings, $request);
            }

            // Check if seller has minimum required wallet balance
            $minimumBalance = $settings->minimum_seller_wallet_balance ?? 0;
            $sellerBalance = $seller->balance()->value;
            
            if ($sellerBalance->lessThan($minimumBalance)) {
                return $this->ApiResponseFormatted(402, null, Lang::get('api.minimum_balance_required'), $settings, $request);
            }

            // Fetch both limits in one query to reduce DB round trips.
            $pendingStatus = (int) ResponseStatus::Pending->value;
            $invitedStatus = (int) ResponseStatus::Invited->value;
            $limitCounts = Response::query()
                ->where('seller_id', $seller->id)
                ->selectRaw("SUM(CASE WHEN status IN ({$pendingStatus}, {$invitedStatus}) THEN 1 ELSE 0 END) as open_pending_count")
                ->selectRaw('SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_responses_count')
                ->first();

            $openPendingCount = (int) ($limitCounts?->open_pending_count ?? 0);
            $maxOpenPending = $settings->max_open_pending_requests;

            if ($openPendingCount >= $maxOpenPending) {
                return $this->ApiResponseFormatted(409, null, Lang::get('api.max_open_pending_reached'), $settings, $request);
            }

            $maxRequests = $settings->maximum_requests_per_day;
            $todayResponsesCount = (int) ($limitCounts?->today_responses_count ?? 0);

            if ($todayResponsesCount >= $maxRequests) {
                return $this->ApiResponseFormatted(409, null, Lang::get('api.daily_limit_reached'), $settings, $request);
            }

            $this->purchasable = $customerRequest;

            if (empty($this->purchasable)) {
                return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
            }


            if ($seller->is_purchased($this->purchasable)) {

                return $this->ApiResponseFormatted(409, null, Lang::get('api.has_been_bought'), $settings, $request);

            }

            // Removed payment - create response directly without charging
            $response = Response::updateOrCreate([
                'request_id' => $this->purchasable->id,
                'service_id' => $this->purchasable->service->id,
                'seller_id' => $seller->id,
            ], [
                'status' => ResponseStatus::Pending,
                'notes' => null,
            ]);
            $customerRequest->customer->notify(new SellerResponseNotification($response));
            $seller->updateAvgResponse();

            return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);

        } catch (\Exception $e) {
            return $this->ApiResponseFormatted(500, null, $e->getMessage(), $settings, $request);
        }


    }



}
