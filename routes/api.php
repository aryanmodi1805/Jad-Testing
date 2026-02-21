<?php

use App\Http\Controllers\AppAuthController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\IosInAppPurchaseController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingsController;
use App\Http\Controllers\RequestsController;
use App\Http\Controllers\ResponsesController;
use App\Http\Controllers\SellerPaymentsController;
use App\Http\Controllers\SellerProfileController;
use App\Http\Controllers\SellerServicesController;
use App\Http\Controllers\SellerWizardController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::get('/test/{id}', function ($id) {
//    return dispatch(new ReviewRequestAIJob(CustomerAnswer::where('request_id', $id)->whereNotNull('voice_note')->first()));
//});

Route::controller(AppAuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('login-phone', 'loginByPhone');
    Route::post('login-phone/request-otp', 'requestLoginOtp');
    Route::post('login-phone/verify-otp', 'verifyLoginOtp');
    Route::post('register', 'register');
    Route::post('forgot', 'forgot');
    Route::post('logout', 'logout');
    Route::post('customer/verify', 'verify');
    Route::post('seller/verify', 'verify');
    Route::post('customer/verify_status', 'verifyStatus');
    Route::post('seller/verify_status', 'verifyStatus');
    Route::post('customer/resend', 'resend');
    Route::post('seller/resend', 'resend');

    Route::post('customer/change_phone', 'changePhone');
    Route::post('seller/change_phone', 'changePhone');

    Route::post('customer/change_password', 'changePassword');
    Route::post('seller/change_password', 'changePassword');

    Route::post('customer/change_profile', 'changeProfile');
    Route::post('seller/change_profile', 'changeProfile');
    Route::post('customer/change_avatar', 'changeAvatar');
    Route::post('seller/change_avatar', 'changeAvatar');

    Route::post('customer/delete', 'deleteCustomerAccount');
    Route::post('seller/delete', 'deleteSellerAccount');

});

// Admin Login Route
Route::post('admin/login', [\App\Http\Controllers\AdminLoginController::class, 'adminLogin']);



// App Store Server Notifications V2 webhook (no authentication required)
Route::post('ios/webhook', [IosInAppPurchaseController::class, 'handleServerNotification']);

// Seller Wizard Registration Routes
Route::controller(SellerWizardController::class)->group(function () {
    Route::post('seller/wizard/register', 'registerSellerWizard');
    Route::post('seller/wizard/test', 'testRequest'); // Debug route
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(SellerWizardController::class)->group(function () {
        Route::post('seller/wizard/progress', 'getRegistrationProgress');
        Route::post('seller/wizard/update', 'updateWizardData');
    });
});

Route::group(['prefix' => 'customer'], function () {
    Route::controller(RequestsController::class)->group(function () {
        Route::get('user-requests', 'getUserRequests');
        Route::get('request/{id}/responses', 'getRequestResponses');
        Route::get('requests/{id}/matchingSellers', 'getMatchingSellers');
        Route::Post('requests/{id}/invite', 'invite');
        Route::Post('requests/create', 'createRequest');


    });

    Route::controller(ResponsesController::class)->group(function () {
        Route::get('responses/{id}/approve', 'approveResponse');
        Route::get('responses/{id}/cancel_invite', 'cancelInvite');
        Route::get('responses/{id}/chat', 'getChatMessages');
        Route::post('responses/{id}/chat/create', 'createChatMessage');
        Route::get('responses/blockReasons', 'getBlockReasons');
        Route::post('responses/{id}/block', 'block');
        Route::get('responses/{id}/pay', 'pay');
    });

    Route::controller(RatingsController::class)->group(function () {
        Route::post('rate', 'rate');
    });


    Route::controller(SellerProfileController::class)->group(function () {
        Route::get('seller/{id}/profile', 'getSellerProfile');
    });

    Route::controller(ProfileController::class)->group(function () {
        Route::get('profile', 'getCustomerProfile');
    });

    Route::controller(NotificationsController::class)->group(function () {
        Route::get('notifications', 'getNotifications');
        Route::get('notifications/unread-count', 'getUnreadCount');
        Route::delete('notifications/{id}', 'deleteNotification');
        Route::post('notifications/{id}/mark-as-read', 'markAsRead');
        Route::post('notifications/mark-all-as-read', 'markAllAsRead');

        Route::get('notifications/settings', 'getCustomerNotificationsSettings');
        Route::post('notifications/settings', 'updateCustomerNotificationsSettings');
    });

    // Payment Verification
    Route::post('verify-payment', [\App\Http\Controllers\PaymentController::class, 'verifyPayment']);

});


Route::group(['prefix' => 'seller'], function () {
    Route::controller(SellerProfileController::class)->group(function () {
        Route::get('profile', 'getProfile');
        Route::get('my-reviews', 'getMyReviews');
        Route::get('my-services', 'getMyServices');
        Route::post('save_services', 'saveMyServices');
        Route::post('delete_services', 'deleteMyServices');
        Route::get('gallery', 'getGallery');
        Route::post('gallery', 'saveGallery');
        Route::post('gallery/delete', 'deleteGallery');
        Route::get('services', 'getSellerServices');
        Route::get('services/all', 'getServices');
        Route::post('services', 'addServicesToSeller');
        Route::get('projects','getSellerProjects');
        Route::post('projects','addSellerProject');
        Route::post('projects/delete','deleteSellerProject');
        Route::get('qas','getSellerQAs');
        Route::post('qas','saveSellerQAs');
    });

    Route::controller(SellerPaymentsController::class)->group(function () {
        Route::get('charge-credit', 'chargeCredit');
        Route::get('subscribe', 'subscribe');
        Route::post('verify-payment', [\App\Http\Controllers\PaymentController::class, 'verifyPayment']);
    });

    // iOS In-App Purchase routes (for sellers)
    Route::controller(IosInAppPurchaseController::class)->group(function () {
        Route::get('ios/products', 'getProducts');
        Route::post('ios/validate-purchase', 'validatePurchase');
    });

    Route::controller(SellerServicesController::class)->group(function () {
        Route::get('getServices', 'getServices');
        Route::post('delete_service', 'removeService');
        Route::get('locations', 'getLocations');
        Route::post('location', 'addLocation');
        Route::post('location/delete', 'removeLocation');

    });


    Route::controller(ResponsesController::class)->group(function () {
        Route::get('responses', 'getSellerResponses');
        Route::get('responses/{id}/chat', 'getChatMessages');
        Route::post('responses/{id}/chat/create', 'createChatMessage');
        Route::get('responses/blockReasons', 'getBlockReasons');
        Route::post('responses/{id}/block', 'block');
        Route::get('estimate-bases', 'getEstimateBases');
        Route::post('responses/{id}/estimate', 'estimate');
    });

    Route::controller(RequestsController::class)->group(function () {
        Route::get('requests', 'getRequestsForSeller');
        Route::get('requests/{id}', 'getRequestDetails');
        Route::get('requests/{id}/not-interested', 'notInterested');
        Route::get('requests/{id}/cancel-invite', 'cancelInvitation');
        Route::get('requests/{id}/contact', 'contact');

    });

    Route::controller(RatingsController::class)->group(function () {
        Route::post('rate', 'rate');
    });

    Route::controller(NotificationsController::class)->group(function () {
        Route::get('notifications', 'getNotifications');
        Route::get('notifications/unread-count', 'getUnreadCount');
        Route::delete('notifications/{id}', 'deleteNotification');
        Route::post('notifications/{id}/mark-as-read', 'markAsRead');
        Route::post('notifications/mark-all-as-read', 'markAllAsRead');

        Route::get('notifications/settings', 'getCustomerNotificationsSettings');
        Route::post('notifications/settings', 'updateCustomerNotificationsSettings');

        Route::get('notifications/not/{id}', 'not');
    });



    Route::controller(WalletController::class)->group(function () {
        Route::get('balance', 'getSellerBalance');
        Route::get('transactions', 'getSellerTransactions');
    });
    
    // New Wallet Charging Routes (Predefined Amounts)
    Route::controller(\App\Http\Controllers\ChargeWalletController::class)->group(function () {
        Route::get('wallet/predefined-amounts', 'getPredefinedAmounts');
        Route::post('wallet/charge', 'initiatePayment');
        Route::get('wallet/payment-methods', 'getPaymentMethods');
    });

    
    // Pending Payment Verification Routes
    Route::controller(\App\Http\Controllers\Api\PendingPaymentController::class)->group(function () {
        Route::get('pending-payment/check', 'checkPending');
        Route::post('pending-payment/{chargeId}/verify', 'manualVerify');
        Route::get('pending-payment/{chargeId}/status', 'getStatus');
    });
});

// Public Callback Route for Payment Gateway (No Auth Required)
Route::get('wallet/payment-callback', [\App\Http\Controllers\ChargeWalletController::class, 'handlePaymentCallback']);

Route::controller(GuestController::class)->group(function () {
    Route::get('services/most-requested', 'mostRequested');
    Route::get('services/hot', 'getHotServices');
    Route::get('services/latest', 'getLatestServices');
    Route::get('home/init', 'getHomeInit');
    Route::get('home/hero', 'getHeroAttributes');
    Route::get('countries', 'countries');
    Route::get('services/wizard/{id}', 'serviceQuestions');
    Route::get('services/search', 'serviceSearch');
    Route::get('services/search-suggestions', 'searchSuggestions');
    Route::get('support', 'getSupport');
    Route::get('services', 'getServices');
    Route::get('categories', 'getCategories');
    Route::get('categories/{id}/services', 'getCategoryServices');
});


