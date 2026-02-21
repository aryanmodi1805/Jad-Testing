<?php

namespace App\Http\Controllers;

use App\Enums\MessageType;
use App\Enums\RequestStatus;
use App\Enums\ResponseStatus;
use App\Events\MessageEvent;
use App\Events\RefreshRequestEvent;
use App\Events\RefreshResponseEvent;
use App\Http\Resources\MessageResource;
use App\Http\Resources\NameResource;
use App\Http\Resources\ResponseResource;
use App\Models\BlockReason;
use App\Models\BlockReport;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\EstimateBase;
use App\Models\Message;
use App\Models\Response;
use App\Models\Seller;
use App\Settings\AppSettings;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Lang;
use App\Services\Payment\Tap;
use App\Models\PaymentMethod;

class ResponsesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function approveResponse(Request $request, AppSettings $settings, $id)
    {
        $response = Response::find($id);

        if ($response == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        try {
            $theRequest = $response->request;

            if (!$response->is_approved && $theRequest->status !== RequestStatus::Completed) {
                
                $paymentMethod = $request->input('payment_method', 'online'); // Default to online if not specified

                if ($paymentMethod === 'cash') {
                    $estimate = $response->estimate;
                    if (!$estimate) {
                         return $this->ApiResponseFormatted(400, null, "Estimate not found", $settings, $request);
                    }

                    $commissionRate = 0.30; // 30%
                    $commissionAmount = $estimate->amount * $commissionRate;
                    $seller = $response->seller;

                    $currentBalance = $seller->balance() ? (float)(string)$seller->balance()->value : 0.0;

                    if ($currentBalance < $commissionAmount) {
                        return $this->ApiResponseFormatted(400, null, Lang::get('api.insufficient_balance'), $settings, $request);
                    }

                    tx($commissionAmount)
                        ->processor('withdraw')
                        ->from($seller)
                        ->meta([
                            'description' => "Cash Payment Commission: {$theRequest->service?->name} - {$theRequest->customer?->name} (30% of {$estimate->amount})",
                            'response_id' => $response->id,
                            'request_id' => $theRequest->id,
                        ])
                        ->commit();
                } else {
                    $estimate = $response->estimate;
                    if (!$estimate) {
                         return $this->ApiResponseFormatted(400, null, "Estimate not found", $settings, $request);
                    }

                    $earnings = $estimate->amount * 0.70;
                    $seller = $response->seller;

                    tx($earnings)
                        ->processor('deposit')
                        ->to($seller)
                        ->meta([
                            'description' => "Online Payment: {$theRequest->service?->name} - {$theRequest->customer?->name} (70% of {$estimate->amount})",
                            'response_id' => $response->id,
                            'request_id' => $theRequest->id,
                        ])
                        ->commit();
                }

                $response->status = ResponseStatus::Hired;
                $response->is_approved = true;
                $response->save();

                $theRequest->status = RequestStatus::Completed;
                $theRequest->save();
                $theRequest->responses()->where('id', '!=', $response->id)->update(['status' => ResponseStatus::Rejected]);


                $sellerIds = $response->request?->responses()->pluck('seller_id')->toArray();
                try {
                    broadcast(new RefreshResponseEvent($sellerIds))->toOthers();
                } catch (\Throwable $e) {
                    Log::warning('Broadcast failed for approve response', [
                        'response_id' => $response->id,
                        'error' => $e->getMessage(),
                    ]);
                }

            }
        } catch (Exception $ex) {
            Log::error('Approve response failed', ['error' => $ex->getMessage()]);
            return $this->ApiResponseFormatted(500, null, $ex->getMessage(), $settings, $request);
        }

        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
    }

    public function cancelInvite(Request $request, AppSettings $settings, $id)
    {
        $response = Response::find($id);

        if ($response == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        $seller_id = $response->seller_id;
        $response->delete();
        try {
            broadcast(new RefreshRequestEvent([$seller_id]));
        } catch (\Throwable $e) {
            Log::warning('Broadcast failed for cancel invite', [
                'seller_id' => $seller_id,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);

    }

    public function getChatMessages(Request $request, AppSettings $settings, $id)
    {
        $sellerResponse = Response::find($id);

        if ($sellerResponse == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        if (($request->user() instanceof Customer && $sellerResponse->request->customer_id != $request->user()->id) ||
            ($request->user() instanceof Seller && $sellerResponse->seller_id != $request->user()->id)) {
            return $this->ApiResponseFormatted(403, null, Lang::get('api.unauthorized'), $settings, $request);
        }

        $limit = max(1, min((int) $request->integer('limit', 50), 100));
        $messages = $sellerResponse->messages()->limit($limit)->get();

        return $this->ApiResponseFormatted(200, MessageResource::collection($messages), Lang::get('api.success'), $settings, $request);
    }

    public function createChatMessage(Request $request, AppSettings $settings, $id)
    {
        if ($request->chat == null) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        $chat = json_decode($request->chat, true);

        if (isset($request->allFiles()['files'])) {
            $requestFiles = $request->allFiles()['files'];

            foreach ($requestFiles as $file) {
                /* @var $file UploadedFile */
                $files ??= [];
                $files[] = $file->storeAs('chat_attachments', "attachment_" . uniqid() . "." . $file->extension(), 'public');
            }
        }

        $sellerResponse = Response::find($id);

        if ($sellerResponse == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        if (($request->user() instanceof Customer && $sellerResponse->request->customer_id != $request->user()->id) ||
            ($request->user() instanceof Seller && $sellerResponse->seller_id != $request->user()->id)) {
            return $this->ApiResponseFormatted(403, null, Lang::get('api.unauthorized'), $settings, $request);
        }

        // Allow empty message if attachments are present
        $messageText = $chat['message'] ?? '';
        if (empty($messageText) && !isset($files)) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        $newMessage = $sellerResponse->messages()->create([
            'message' => $messageText,
            'sender_id' => $request->user()->id,
            'sender_type' => get_class($request->user()),
            'attachments' => $files ?? null,
        ]);

        try {
            broadcast(new MessageEvent(
                $sellerResponse->id,
                $newMessage->id,
                $newMessage->sender_id,
            ));
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }

        $messages = $sellerResponse->messages()->limit(50)->get();

        return $this->ApiResponseFormatted(200, MessageResource::collection($messages), Lang::get('api.success'), $settings, $request);
    }

    public function getBlockReasons(Request $request, AppSettings $settings)
    {
        // Cache only columns needed by NameResource (id, name)
        $blockReasons = \Cache::remember('api_block_reasons', 60 * 60 * 24, function () {
            return BlockReason::select('id', 'name')->get();
        });
        
        return $this->ApiResponseFormatted(200, NameResource::collection($blockReasons), Lang::get('api.success'), $settings, $request);
    }


    public function block(Request $request, AppSettings $settings, $id)
    {
        $validate = Validator::make($request->all(), [
            'blockReason' => 'required',
        ]);

        if ($validate->fails()) {
            return $this->ApiResponseFormatted(422, null, Lang::get('api.validation_error'), $settings, $request);
        }

        $blockReason = BlockReason::find($request->blockReason['id']);

        if ($blockReason == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        $sellerResponse = Response::find($id);

        if ($sellerResponse == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        if ($request->user() instanceof Customer && $sellerResponse->request->customer_id != $request->user()->id) {
            return $this->ApiResponseFormatted(403, null, Lang::get('api.unauthorized'), $settings, $request);
        } elseif ($request->user() instanceof Seller && $sellerResponse->seller_id != $request->user()->id) {
            return $this->ApiResponseFormatted(403, null, Lang::get('api.unauthorized'), $settings, $request);
        }

        if ($request->user() instanceof Customer) {
            $otherUser = $sellerResponse->seller;

            $responses = Response::query()
                ->join('requests', 'responses.request_id', '=', 'requests.id')
                ->where('requests.customer_id', $request->user()->id)
                ->where('responses.status', ResponseStatus::Pending)
                ->where('seller_id', '=', $otherUser->id);

            Response::whereIn('id', $responses->pluck('responses.id'))->update([
                'status' => ResponseStatus::Rejected
            ]);
        } else {
            $otherUser = $sellerResponse->request->customer;

            $responses = Response::query()
                ->join('requests', 'responses.request_id', '=', 'requests.id')
                ->where('requests.customer_id', $otherUser->id)
                ->where('responses.status', ResponseStatus::Pending)
                ->where('seller_id', '=', $request->user()->id);

            Response::whereIn('id', $responses->pluck('responses.id'))->update([
                'status' => ResponseStatus::Rejected
            ]);
        }


        BlockReport::create([
            'reference_id' => $sellerResponse->id,
            'reference_type' => Response::class,
            'blocked_id' => $otherUser->id,
            'blocked_type' => $otherUser::class,
            'blocker_id' => $request->user()->id,
            'blocker_type' => $request->user()::class,
            'block_reason_id' => $blockReason->id,
            'details' => $request->details ?? null,
        ]);

        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
    }

    public function getSellerResponses(Request $request, AppSettings $settings)
    {
        $status = $request->status;
        $page = $request->page ?? 1;
        $sellerId = $request->user()->id;
        
        // Eager load all relations used by ResponseResource to prevent N+1 queries
        // Include seller.services and seller.ratings for SellerResource
        $query = Response::whereBelongsTo($request->user())
            ->with([
                'service',
                'seller.services', 
                'seller.ratings',
                'customer',
                'estimate.estimateBase',
                'request.customer',
                'request.service'
            ])
            ->orderBy('created_at', 'desc');

        $sellerResponsesQuery = $query->clone();

        if ($status !== null) {
            $sellerResponsesQuery->whereIn('status', (array)$status);
        }

        $records = $sellerResponsesQuery->paginate(8, page: $page);

        // Only compute status counts on first page or if not cached (expensive query)
        // Cache for 5 minutes to avoid repeated computation
        $statusCounts = [];
        $responsesCount = 0;
        
        if ($page == 1) {
            $cacheKey = "seller_responses_status_count_{$sellerId}_v1";

            if ($request->refresh) {
                \Cache::forget($cacheKey);
            }

            $cached = \Cache::remember($cacheKey, 300, function () use ($request, $query) {
                // Get counts from database
                $dbCounts = Response::whereBelongsTo($request->user())
                    ->groupBy('status')
                    ->selectRaw('count(*) as count, status')
                    ->pluck('count', 'status');

                // Initialize all statuses with 0
                $allStatuses = [
                    ResponseStatus::Pending->value => 0,
                    ResponseStatus::Rejected->value => 0,
                    ResponseStatus::Hired->value => 0,
                ];

                // Merge database counts
                foreach ($dbCounts as $status => $count) {
                    // Cast status to int as enum cases are int backed
                    $status = (int)$status;
                    if (isset($allStatuses[$status])) {
                        $allStatuses[$status] = (int)$count;
                    }
                }

                // Format for response
                $counts = [];
                foreach ($allStatuses as $key => $value) {
                    $counts[] = [
                        'status' => $key,
                        'count' => $value,
                    ];
                }
                
                return [
                    'statusCounts' => $counts,
                    'total' => $query->count()
                ];
            });
            
            $statusCounts = $cached['statusCounts'];
            $responsesCount = $cached['total'];
        }

        return $this->ApiResponseFormatted(200, [
            'responses' => ResponseResource::collection($records->items()),
            'statusCount' => $statusCounts,
            'responses_count' => $responsesCount
        ], Lang::get('api.success'), $settings, $request);

    }

    public function estimate(Request $request, AppSettings $settings, $id)
    {
        $response = Response::find($id);

        if ($response == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        if ($request->user() instanceof Seller && $response->seller_id != $request->user()->id) {
            return $this->ApiResponseFormatted(403, null, Lang::get('api.unauthorized'), $settings, $request);
        }

        if ($response->status === ResponseStatus::Hired || $response->request->status === RequestStatus::Completed) {
            return $this->ApiResponseFormatted(400, null, Lang::get('api.invoice_update_restricted'), $settings, $request);
        }

        $validate = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'estimateBaseId' => 'required|exists:estimate_bases,id',
            'details' => 'required|string',
        ]);

        if ($validate->fails()) {
            return $this->ApiResponseFormatted(422, $validate->errors(), Lang::get('api.validation_error'), $settings, $request);
        }

        $estimate = $response->estimate()->updateOrCreate([
            'response_id' => $response->id,
        ], [
            'amount' => $request->amount,
            'estimate_base_id' => $request->estimateBaseId,
            'details' => $request->details,
        ]);

        $newMessage = Message::create([
            'response_id' => $response->id,
            'sender_id' => auth()->id(),
            'sender_type' => Seller::class,
            'message' => 'Estimate updated',
            'payload' => [
                'type' => MessageType::Estimate,
                'data' => $estimate->load('estimateBase')
            ],
            'type_type' => Estimate::class,

        ]);


        try {
            broadcast(new MessageEvent(
                $response->id,
                $newMessage->id,
                $newMessage->sender_id,
            ));
        } catch (\Throwable $e) {
            // Log broadcast failure but don't fail the API - the estimate was already updated
            Log::warning('Broadcast failed for estimate update', [
                'response_id' => $response->id,
                'message_id' => $newMessage->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->ApiResponseFormatted(200, null, Lang::get('api.success'), $settings, $request);
    }

    public function getEstimateBases(Request $request, AppSettings $settings)
    {
        // Cache only columns needed by NameResource (id, name)
        $estimateBases = \Cache::remember('api_estimate_bases', 60 * 60 * 24, function () {
            return EstimateBase::select('id', 'name')->get();
        });
        
        return $this->ApiResponseFormatted(200, NameResource::collection($estimateBases), Lang::get('api.success'), $settings, $request);
    }


    public function pay(Request $request, AppSettings $settings, $id)
    {
        $response = Response::find($id);

        if ($response == null) {
            return $this->ApiResponseFormatted(404, null, Lang::get('api.record_not_found'), $settings, $request);
        }

        if ($request->user() instanceof Customer && $response->request->customer_id != $request->user()->id) {
            return $this->ApiResponseFormatted(403, null, Lang::get('api.unauthorized'), $settings, $request);
        }

        $estimate = $response->estimate;
        if (!$estimate) {
             return $this->ApiResponseFormatted(400, null, "Estimate not found", $settings, $request);
        }

        try {
            $customer = $request->user();
            
            // Check for existing pending payment
            $pendingPayment = \App\Models\PendingPayment::where('user_id', $customer->id)
                ->whereIn('status', ['pending', 'verifying'])
                ->where('expires_at', '>', now())
                ->first();

            if ($pendingPayment) {
                if (!$request->wantsJson()) {
                    return response(<<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            justify_content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f3f4f6;
            padding: 20px;
        }
        .card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .icon {
            color: #3b82f6;
            margin-bottom: 1rem;
        }
        .title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        .message {
            color: #6b7280;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="title">Payment in Process</div>
        <div class="message">Your payment is in process. Please try again after 5 minutes.</div>
    </div>
</body>
</html>
HTML
                    );
                }

                return $this->ApiResponseFormatted(429, [
                    'pending_payment' => [
                        'charge_id' => $pendingPayment->charge_id,
                        'expires_at' => $pendingPayment->expires_at->toIso8601String(),
                        'seconds_remaining' => $pendingPayment->secondsRemaining()
                    ]
                ], 'You have a pending payment. Please wait for verification.', $settings, $request);
            }
            
            $paymentMethod = PaymentMethod::where('type', Tap::getProviderName())->firstOrFail();
            
            // Calculate amount (full estimate amount is paid by customer)
            $amount = $estimate->amount;
            
            // Use Tap service to create payment URL
            $tapService = new Tap($paymentMethod->details);
            
            $paymentUrl = $tapService->createServicePayment(
                $paymentMethod->id,
                $customer,
                $amount,
                'SAR',
                $response
            );

            if ($paymentUrl) {
                 return redirect($paymentUrl);
            } else {
                 return response('Payment initialization failed', 500);
            }

        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return response($ex->getMessage(), 500);
        }
    }
}
